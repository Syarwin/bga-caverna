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

class PlaceDwarf extends \CAV\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = clienttranslate('Place a person');
  }

  public function getState()
  {
    return ST_PLACE_DWARF;
  }

  public function isDoable($player, $ignoreResources = false)
  {
    return $player->hasDwarfAvailable();
  }

  /**
   * Compute the selectable actions cards/space for active player
   */
  function argsPlaceDwarf()
  {
    $player = Players::getActive();
    $cards = ActionCards::getVisible($player);
    $constraints = $this->getCtxArgs()['constraints'] ?? null;

    $args = [
      'allCards' => $cards->getIds(),
      'cards' => $cards
        ->filter(function ($card) use ($player) {
          return $card->canBePlayed($player);
        })
        ->getIds(),
    ];

    $this->checkArgsModifiers($args, $player);

    if ($constraints != null) {
      $args['cards'] = \array_values(\array_intersect($constraints, $args['cards']));
    }
    return $args;
  }

  /**
   * Place the farmer on a card/space and activate the corresponding card
   *   to update the flow tree
   */
  function actPlaceDwarf($cardId)
  {
    self::checkAction('actPlaceDwarf');
    $player = Players::getActive();

    $cards = self::argsPlaceDwarf()['cards'];
    if (!\in_array($cardId, $cards)) {
      throw new \BgaUserException(clienttranslate('You cannot place a person here'));
    }

    if (in_array($cardId, $player->getActionCards()->getIds())) {
      $card = Buildings::get($cardId);
    } else {
      $card = ActionCards::get($cardId);
    }

    $eventData = [
      'actionCardId' => $card->getId(),
      'actionCardType' => $card->getActionCardType(),
    ];

    // Place farmer
    $fId = $player->moveNextFarmerAvailable($cardId);
    Notifications::placeFarmer($player, $fId, $card, $this->ctx->getSource());
    Stats::incPlacedDwarves($player);

    // Are there cards triggered by the placement ?
    $this->checkListeners('PlaceDwarf', $player, $eventData);

    // Activate action card
    $flow = $card->getFlow($player);
    $this->checkModifiers('computePlaceDwarfFlow', $flow, 'flow', $player, $eventData);

    // D101 side effect
    if (!$card->hasAccumulation() && Meeples::getResourcesOnCard($cardId)->count() > 0) {
      $flow = [
        'type' => NODE_SEQ,
        'childs' => [
          [
            'action' => COLLECT,
            'cardId' => $cardId,
          ],
          $flow,
        ],
      ];
    }
    Engine::insertAsChild($flow);

    $this->checkAfterListeners($player, $eventData, false);
    $this->resolveAction(['actionCardId' => $cardId]);
  }
}
