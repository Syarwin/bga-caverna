<?php
namespace CAV\Actions;

use CAV\Managers\Players;
use CAV\Managers\Meeples;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Core\Globals;
use CAV\Managers\Buildings;

class Reorganize extends \CAV\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = clienttranslate('Reorganize animals');
  }

  public function getState()
  {
    return ST_REORGANIZE;
  }

  public function isDoable($player, $ignoreResources = false)
  {
    return $player->getAnimals()->count() > 0;
  }

  public function argsReorganize()
  {
    $args = $this->ctx->getArgs();
    $trigger = $args['trigger'] ?? ANYTIME;

    $player = Players::getActive();
    return [
      'exchanges' => $player->getExchangeableAnimalTypes($trigger),
      'animals' => $player->getAnimals()->toArray(),
      'zones' => $player->board()->getAnimalsDropZones(),
      'harvest' => $trigger == HARVEST,
      'canGoToReorganize' => false,
      'canGoToExchange' => false,
    ];
  }

  public function actReorganize($meeplesOnBoard, $meeplesInReserve, $meeplesOnCard)
  {
    $player = Players::getActive();
    $args = $this->ctx->getArgs();
    $trigger = $args['trigger'] ?? ANYTIME;
    $breed = $args['breedTypes'] ?? $player->breedTypes();

    $ids = [];
    foreach ($meeplesOnBoard as $meeple) {
      Meeples::moveToCoords($meeple['id'], 'board', $meeple);
      $ids[] = $meeple['id'];
    }

    foreach ($meeplesInReserve as $meeple) {
      Meeples::moveToCoords($meeple['id'], 'reserve');
      $ids[] = $meeple['id'];
    }

    foreach ($meeplesOnCard as $meeple) {
      Meeples::moveToCoords($meeple['id'], $meeple['card_id']);
      $ids[] = $meeple['id'];
    }

    // Checks all zones and animals
    $player->board()->areAnimalsValid();

    // check that we have at least 3 of each on board, else silent kill
    if ($trigger == HARVEST) {
      $silentKill = [];
      $animals = $player->countAnimalsOnBoard();

      foreach ($breed as $animal => $born) {
        if ($born && $animals[$animal] < 3) {
          // destroy the the baby in reserve
          array_push($silentKill, ...$player->useResource($animal, 1));
        }
      }
      if (count($silentKill) != 0) {
        Notifications::silentKill($silentKill);
      }
    }

    Notifications::reorganize($player, Meeples::getAnimals($player->getId()));

    // Still animals in reserve => go to exchange state if canCook
    if (count($meeplesInReserve) > 0) {
      $canCookSomeAnimals = false;
      $exchanges = $player->getExchangeableAnimalTypes();
      $animals = $player->getAllReserveResources();
      foreach ([SHEEP, PIG, CATTLE] as $type) {
        if ($animals[$type] == 0) {
          continue;
        }

        // Can we exchange this type of animals ?
        if ($exchanges[$type]) {
          $canCookSomeAnimals = true;
        } else {
          // We discard the remaining animals as they cannot be cooked
          Notifications::discardAnimals($player, $player->useResource($type, $animals[$type]));
        }
      }

      if ($canCookSomeAnimals) {
        Engine::insertAsChild([
          'action' => EXCHANGE,
          'args' => [
            'mustCook' => true,
          ],
        ]);
      }
    }
    $this->checkAfterListeners($player, [
      'trigger' => $trigger,
    ]);
    $this->resolveAction();
  }

  public static function checkAutoReorganize($player, &$meeples)
  {
    // Are there any animals to check ?
    $atLeastOneAnimal = array_reduce(
      $meeples,
      function ($carry, $meeple) {
        return $carry || in_array($meeple['type'], ANIMALS);
      },
      false
    );
    if (!$atLeastOneAnimal || $player->getPref(OPTION_SMART_REORGANIZE) == OPTION_SMART_REORGANIZE_OFF) {
      return false;
    }

    // Try to find them a nice place
    $zones = $player->board()->getAnimalsDropZonesWithAnimals();

    // Sort pastures first, then stables, then rooms
    usort($zones, function ($a, $b) {
      $map = [
        'D148_special' => 4,
        'pasture' => 3,
        'stable' => 2,
        'card' => 1,
        'room' => 0,
      ];
      return $map[$b['type']] - $map[$a['type']];
    });

    foreach ($meeples as &$meeple) {
      if (!in_array($meeple['type'], ANIMALS) || $meeple['location'] == 'board') {
        continue;
      }

      // Search zone of same animal type with an empty slot
      $sameFound = false;
      foreach ($zones as &$zone) {
        if (
          $zone['type'] == 'card' &&
          method_exists(Buildings::getFilteredQuery(null, null, $zone['card_id'])->get(), 'canAcceptAnimal')
        ) {
          if (
            Buildings::getFilteredQuery(null, null, $zone['card_id'])
              ->get()
              ->canAcceptAnimal($zone, $meeple)
          ) {
            self::placeInZone($player, $meeple, $zone);
            $sameFound = true;
            break;
          }
        } elseif ($zone['animals'] < $zone['capacity'] && $zone[$meeple['type']] > 0) {
          self::placeInZone($player, $meeple, $zone);
          $sameFound = true;
          break;
        }
      }

      // Now search any zone with a free spot
      if (!$sameFound) {
        foreach ($zones as &$zone) {
          if ($zone['animals'] == 0 && in_array($meeple['type'], $zone['constraints'] ?? ANIMALS)) {
            self::placeInZone($player, $meeple, $zone);
            break;
          }
        }
      }
    }

    return $player->getPref(OPTION_SMART_REORGANIZE) == OPTION_SMART_REORGANIZE_CONFIRM;
  }

  protected static function placeInZone($player, &$meeple, &$zone)
  {
    // First find a spot
    $c = null;
    $loc = null;
    foreach ($zone['locations'] as $location) {
      if ($zone['type'] == 'card') {
        $n = Meeples::countAnimalsInZoneCard($player->getId(), $location);
      } else {
        $n = Meeples::countAnimalsInZoneLocation($player->getId(), $location);
      }
      if ($c == null) {
        $c = $n;
      }

      if ($n < $c) {
        $loc = $location;
        break;
      }
    }
    if ($loc == null) {
      $loc = $zone['locations'][0];
    }

    // Place meeple
    if ($zone['type'] == 'card') {
      Meeples::moveToCoords($meeple['id'], $loc['card_id'], null);
      $meeple['location'] = $loc['card_id'];
    } else {
      Meeples::moveToCoords($meeple['id'], 'board', $loc);
      $meeple['location'] = 'board';
      $meeple['x'] = $loc['x'];
      $meeple['y'] = $loc['y'];
    }
    $zone['animals']++;
    $zone[$meeple['type']] = ($zone[$meeple['type']] ?? 0) + 1;
  }
}
