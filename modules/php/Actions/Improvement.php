<?php
namespace CAV\Actions;
use CAV\Managers\Buildings;
use CAV\Managers\Players;
use CAV\Helpers\Collection;
use CAV\Core\Stats;

class Improvement extends \CAV\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
  }

  public function getState()
  {
    return ST_IMPROVEMENT;
  }

  public function getTypes($player = null)
  {
    $types = $this->getCtxArgs()['types'];
    if ($player != null && $player->hasPlayedCard('C27_Blueprint') && !in_array(MAJOR, $this->getTypes())) {
      $types[] = MAJOR;
    }
    return $types;
  }

  public function getDescription($ignoreResources = false, $short = false)
  {
    $types = $this->getTypes();
    if ($types == [MAJOR]) {
      return $short ? clienttranslate('major') : clienttranslate('Build a major improvement');
    }
    if ($types == [MINOR]) {
      return $short ? clienttranslate('minor') : clienttranslate('Build a minor improvement');
    }
    if ($types == [MINOR, MAJOR]) {
      return $short ? clienttranslate('major/minor') : clienttranslate('Build a major or minor improvement');
    }
  }

  public function getAvailableCards()
  {
    $cards = new Collection();
    $types = $this->getTypes();
    foreach ($types as $type) {
      $cards = $cards->merge(Buildings::getAvailables($type));
    }
    return $cards;
  }

  public function getBuyableCards($player, $ignoreResources = false)
  {
    $args = [
      'actionCardId' => $this->ctx != null ? $this->ctx->getCardId() : null,
    ];

    $buy = $this->getAvailableCards()->filter(function ($imp) use ($player, $ignoreResources, $args) {
      return $imp->isBuyable($player, $ignoreResources, $args);
    });

    if ($player->hasPlayedCard('C27_Blueprint') && !in_array(MAJOR, $this->getTypes())) {
      $cardList = ['Major_Joinery', 'Major_Pottery', 'Major_Basket'];
      foreach ($cardList as $card) {
        if (Buildings::get($card)->isBuyable($player, $ignoreResources, $args)) {
          $buy[$card] = Buildings::get($card);
        }
      }
    }
    return $buy;
  }

  public function isDoable($player, $ignoreResources = false)
  {
    return !$this->getBuyableCards($player, $ignoreResources)->empty();
  }

  public function argsImprovement()
  {
    $player = Players::getActive();

    return [
      'i18n' => ['strTypes'],
      'strTypes' => $this->getDescription(false, true),
      'types' => $this->getTypes($player),
      '_private' => [
        'active' => [
          'cards' => $this->getBuyableCards($player)->getIds(),
        ],
      ],
    ];
  }

  public function actImprovement($cardId)
  {
    self::checkAction('actImprovement');
    $player = Players::getActive();
    // Sanity check on card
    $cards = $this->getBuyableCards($player);
    if (!$cards->offsetExists($cardId)) {
      throw new \BgaVisibleSystemException('You can\'t play this improvement');
    }

    $card = $cards[$cardId];
    $eventData = [
      'cardId' => $cardId,
      'types' => $this->getTypes(),
      'actionCardId' => $this->ctx != null ? $this->ctx->getCardId() : null,
    ];
    $card->actBuy($player, $eventData);
    if ($card->getType() == MAJOR) {
      Stats::incTotalMajorBuilt($player);
    } else {
      Stats::incTotalMinorBuilt($player);
    }

    $this->checkAfterListeners($player, $eventData);
    $this->resolveAction(['cardId' => $cardId]);
  }
}
