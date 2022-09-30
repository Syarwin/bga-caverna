<?php
namespace CAV\Actions;

use CAV\Managers\Players;
use CAV\Managers\ActionCards;
use CAV\Managers\Buildings;
use CAV\Managers\Meeples;
use CAV\Managers\Dwarfs;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Core\Globals;
use CAV\Core\Stats;
use CAV\Helpers\Utils;

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
    } else {
      $dwarf = Dwarfs::get($dwarf);
    }

    $weapon = $dwarf['weapon'] ?? 0;
    $args = [
      'i18n' => ['dwarfDesc'],
      'dwarf' => $dwarf,
      'possibleWeapons' => $possibleWeapons,
      'hasRuby' => $player->countReserveResource(RUBY) != 0,
      'allCards' => $cards->getIds(),
      'cards' => $cards
        ->filter(function ($card) use ($player, $dwarf) {
          return $card->canBePlayed($player, $dwarf);
        })
        ->getIds(),
      'weapon' => $weapon,
      'dwarfDesc' =>
        $weapon == 0
          ? clienttranslate('unarmed')
          : ['log' => clienttranslate('${force} weapon strength'), 'args' => ['force' => $weapon]],
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
  function actPlaceDwarf($cardId, $dwarfId = null)
  {
    self::checkAction('actPlaceDwarf');
    $player = Players::getActive();

    if (is_null($dwarfId)) {
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
    } else {
      // Player wants to place another dwarf
      $dwarf = Dwarfs::get($dwarfId);
      if (is_null($dwarf) || $dwarf['pId'] != $player->getId() || $dwarf['location'] != 'board') {
        throw new \BgaVisibleSystemException('You cannot use this dwarf. Should not happen');
      }

      Engine::insertAsChild([
        'type' => NODE_SEQ,
        'childs' => [
          [
            'action' => PAY,
            'args' => [
              'costs' => Utils::formatCost([RUBY => 1]),
              'nb' => 1,
              'source' => clienttranslate('placing an equipped dwarf early'),
            ],
          ],
          ['action' => \PLACE_DWARF, 'args' => ['dwarf' => $dwarfId]],
        ],
      ]);
      $this->resolveAction(['action' => 'pay1ruby']);
    }
  }
}
