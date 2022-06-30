<?php
namespace AGR\Actions;

use AGR\Helpers\Utils;
use AGR\Managers\PlayerCards;
use AGR\Managers\Players;
use AGR\Core\Stats;

class Occupation extends \AGR\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
  }

  public function getState()
  {
    return ST_OCCUPATION;
  }

  public function getDescription($ignoreResources = false)
  {
    $cost = $this->getCost();
    if (empty($cost)) {
      return clienttranslate('Play an occupation');
    } else {
      return [
        'log' => clienttranslate('Play an occupation for ${resources_desc}'),
        'args' => [
          'resources_desc' => Utils::resourcesToStr($cost),
        ],
      ];
    }
  }

  public function getAvailableCards()
  {
    return PlayerCards::getAvailables(OCCUPATION);
  }

  public function getBuyableCards($player, $ignoreResources = false)
  {
    $args = [
      'actionCardId' => $this->ctx != null ? $this->ctx->getCardId() : null,
    ];

    return $this->getAvailableCards()->filter(function ($occ) use ($player, $ignoreResources, $args) {
      return $occ->isBuyable($player, $ignoreResources, $args);
    });
  }

  public function isDoable($player, $ignoreResources = false)
  {
    return !$this->getBuyableCards($player, $ignoreResources)->empty() &&
      ($ignoreResources || $player->canPayCost($this->getCost()));
  }

  public function getCost()
  {
    return $this->getCtxArgs()['cost'] ?? [];
  }

  public function stOccupation()
  {
    $player = Players::getActive();
    if (!$player->canPayCost($this->getCost())) {
      throw new \BgaVisibleSystemException('You can\'t afford occupation'); // TODO : translate
    }
  }

  public function argsOccupation()
  {
    $player = Players::getActive();

    return [
      'strTypes' => $this->getDescription(true),
      'types' => [OCCUPATION],
      '_private' => [
        'active' => [
          'cards' => $this->getBuyableCards($player)->getIds(),
        ],
      ],
    ];
  }

  public function actOccupation($cardId)
  {
    self::checkAction('actOccupation');
    $player = Players::getActive();
    // Sanity check on card
    $cards = $this->getBuyableCards($player);
    if (!$cards->offsetExists($cardId)) {
      throw new \BgaVisibleSystemException('You can\'t play this occupation');
    }

    $card = $cards[$cardId];
    if (!empty($this->getCost())) {
      $args = $this->getCtxArgs();
      $player->pay(1, Utils::formatCost($this->getCost()), $args['source'] ?? clienttranslate('Lessons'));
    }

    $card->actBuy($player);
    Stats::incTotalOccupationBuilt($player);
    $eventData = ['cardId' => $cardId, 'costs' => $this->getCost()];
    $this->checkAfterListeners($player, $eventData);
    $this->resolveAction(['cardId' => $cardId]);
  }
}
