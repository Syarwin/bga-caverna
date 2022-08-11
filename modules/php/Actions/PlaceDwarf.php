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
    $this->description = clienttranslate('Place a dwarf');
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

    // Compute possible weapons for dwarf
    $dwarfs = $player->getAvailableDwarfs();
    $possibleWeapons = [];
    foreach ($dwarfs as $d) {
      $weapon = $d['weapon'] ?? 0;
      if (!isset($possibleWeapons[$weapon])) {
        $possibleWeapons[$weapon] = $d;
      }
    }
    ksort($possibleWeapons);
    // Take the most armed dwarf if not already enforced by using a ruby
    $dwarf = $this->getCtxArgs()['dwarf'] ?? null;
    if (is_null($dwarf)) {
      $dwarf = reset($possibleWeapons);
    }

    // TODO: manage ruby possibility to use another dwarf

    $args = [
      'dwarf' => $dwarf,
      'possibleWeapons' => $possibleWeapons,
      'allCards' => $cards->getIds(),
      'cards' => $cards
        ->filter(function ($card) use ($player, $dwarf) {
          return $card->canBePlayed($player, $dwarf);
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
   * Place the dwarf on a card/space and activate the corresponding card
   *   to update the flow tree
   */
  function actPlaceDwarf($cardId)
  {
    self::checkAction('actPlaceDwarf');
    $player = Players::getActive();

    $args = self::argsPlaceDwarf();
    $cards = $args['cards'];
    if (!\in_array($cardId, $cards)) {
      throw new \BgaUserException(clienttranslate('You cannot place a person here'));
    }

    $card = ActionCards::get($cardId);
    $eventData = [
      'actionCardId' => $card->getId(),
      'actionCardType' => $card->getActionCardType(),
    ];

    // Place dwarf
    $dwarf = $args['dwarf'];
    $dwarfId = $dwarf['id'];
    Meeples::moveToCoords($dwarfId, $cardId);
    Notifications::placeDwarf($player, $dwarfId, $card, $this->ctx->getSource());
    Stats::incPlacedDwarfs($player);

    // Are there cards triggered by the placement ?
    $this->checkListeners('PlaceDwarf', $player, $eventData);

    // Activate action card
    $flow = $card->getTaggedFlow($player, $dwarf);
    $this->checkModifiers('computePlaceDwarfFlow', $flow, 'flow', $player, $eventData);
    Engine::insertAsChild($flow);

    $this->checkAfterListeners($player, $eventData, false);
    $this->resolveAction(['actionCardId' => $cardId, 'dwarfId' => $dwarfId]);
  }
}
