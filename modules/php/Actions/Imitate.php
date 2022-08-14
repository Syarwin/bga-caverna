<?php
namespace CAV\Actions;

use CAV\Managers\Players;
use CAV\Managers\ActionCards;
use CAV\Managers\Buildings;
use CAV\Managers\Meeples;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Core\Globals;
use CAV\Core\Stats;

class Imitate extends \CAV\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = clienttranslate('Copy another used action space (except first player action)');
  }

  public function getState()
  {
    return ST_IMITATION;
  }

  public function isDoable($player, $ignoreResources = false)
  {
    return !empty($this->getCopiableCards($player, null, $ignoreResources));
  }

  function getCopiableCards($player, $dwarf, $ignoreResources = false)
  {
    $cards = ActionCards::getVisible($player);
    return $cards
      ->filter(function ($card) use ($player, $dwarf, $ignoreResources) {
        return $card->canBeCopied($player, $dwarf, $ignoreResources);
      })
      ->getIds();
  }

  public function argsImitate()
  {
    $player = Players::getActive();
    $dwarf = $this->getDwarf();
    $cards = ActionCards::getVisible($player);
    return [
      'allCards' => $cards->getIds(),
      'cards' => $this->getCopiableCards($player, $dwarf),
    ];
  }

  public function actImitate($cardId)
  {
    self::checkAction('actImitate');
    $player = Players::getCurrent();

    $args = self::argsImitate();
    $cards = $args['cards'];
    if (!\in_array($cardId, $cards)) {
      throw new \BgaUserException(clienttranslate('You cannot imitate this card'));
    }

    $dwarf = $this->getDwarf();
    $card = ActionCards::get($cardId);
    $eventData = [
      'actionCardId' => $card->getId(),
      'actionCardType' => $card->getActionCardType(),
      'imitate' => true,
    ];

    Notifications::imitate($player, $card);
    
    // Copy action card
    $flow = $card->getTaggedFlow($player, $dwarf);
    $this->checkModifiers('computePlaceDwarfFlow', $flow, 'flow', $player, $eventData);
    Engine::insertAsChild($flow);

    $this->checkAfterListeners($player, $eventData, false);
    $this->resolveAction(['actionCardId' => $cardId]);
  }
}
