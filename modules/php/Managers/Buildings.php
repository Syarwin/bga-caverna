<?php
namespace CAV\Managers;
use CAV\Core\Globals;
use CAV\Helpers\Utils;

/* Class to manage all the buildings for Caverna */

class Buildings extends \CAV\Helpers\Pieces
{
  protected static $table = 'buildings';
  protected static $prefix = 'building_';
  protected static $customFields = ['player_id', 'extra_datas', 'type', 'x', 'y'];
  protected static $autoremovePrefix = false;

  protected static function cast($building)
  {
    return self::getBuildingInstance($building['type'], $building);
  }

  public static function getBuildingInstance($type, $data = null)
  {
    $t = explode('_', $type);
    // First part before _ specify the type of Building
    // Eg: D for Dwellings
    $prefix = $t[0];
    $className = "\CAV\Buildings\\$prefix\\$type";
    return new $className($data);
  }

  /* Creation of the cards */
  public static function setupNewGame($players, $options)
  {
    // Load list of cards
    include dirname(__FILE__) . '/../Buildings/list.inc.php';

    $buildings = [];
    foreach ($buildingIds as $cId) {
      $building = self::getBuildingInstance($cId);
      if ($building->isSupported($players, $options)) {
        $buildings[] = [
          'type' => $building->getType(),
          'location' => 'board',
        ];
      }
    }

    foreach ($players as $pId => $player) {
      $buildings[] = [
        'type' => 'D_StartDwelling',
        'player_id' => $pId,
        'location' => 'inPlay',
        'x' => 7,
        'y' => 7,
        'nbr' => 1,
      ];
    }

    // Create the buildings
    self::create($buildings);
  }

  public static function createDwelling()
  {
    return self::singleCreate([
      'type' => 'D_Dwelling',
      'location' => 'inPlay',
    ]);
  }

  public static function getUiData()
  {
    return self::getInLocationOrdered('board')
      ->merge(self::getInLocationOrdered('inPlay'))
      ->ui();
  }

  /**
   * Generic base query
   */
  public function getFilteredQuery($pId, $location, $type)
  {
    $query = self::getSelectQuery()->wherePlayer($pId);
    if ($location != null) {
      $query = $query->where('building_location', $location);
    }
    if ($type != null) {
      if (is_array($type)) {
        $query = $query->whereIn('type', $type);
      } else {
        $query = $query->where('type', strpos($type, '%') === false ? '=' : 'LIKE', $type);
      }
    }
    return $query;
  }

  public static function getOfPlayer($pId)
  {
    return self::getFilteredQuery($pId, 'inPlay', null)->get();
  }

  /**************************** Dwellings *****************************************/
  protected function getDwellingsQ($pId)
  {
    return self::getFilteredQuery($pId, 'board', 'D_%');
  }

  public function getDwellings($pId)
  {
    return self::getDwellingsQ($pId)->get();
  }

  // countRooms
  public function countDwelings($pId)
  {
    $room = 0;
    return self::getDwellingsQ($pId)->accumulate($room, function ($b) {
      return $b->getDwellingCapacity();
    });
  }

  public static function getAvailables($types = null)
  {
    return self::getInLocation('board')->filter(function ($building) use ($types) {
      return $types == null || in_array($building->getType(), $types);
    });
  }

  /**
   * Get all the cards triggered by an event
   */
  public function getListeningBuildings($event)
  {
    return self::getInLocation('inPlay')
      ->merge(self::getInLocation('hand'))
      ->filter(function ($building) use ($event) {
        return $building->isListeningTo($event);
      })
      ->getIds();
  }

  /**
   * Get reaction in form of a PARALLEL node with all the activated card
   */
  public function getReaction($event, $returnNullIfEmpty = true)
  {
    $listeningBuildings = self::getListeningBuildings($event);
    if (empty($listeningBuildings) && $returnNullIfEmpty) {
      return null;
    }

    $childs = [];
    $passHarvest = Globals::isHarvest() ? Globals::getSkipHarvest() ?? [] : [];
    foreach ($listeningBuildings as $buildingId) {
      if (
        in_array(
          self::get($buildingId)
            ->getPlayer()
            ->getId(),
          $passHarvest
        )
      ) {
        continue;
      }

      $childs[] = [
        'action' => ACTIVATE_BUILDING,
        'pId' => $event['pId'],
        'args' => [
          'cardId' => $buildingId,
          'event' => $event,
        ],
      ];
    }

    if (empty($childs) && $returnNullIfEmpty) {
      return null;
    }

    return [
      'type' => NODE_PARALLEL,
      'pId' => $event['pId'],
      'childs' => $childs,
    ];
  }

  /**
   * Go trough all played cards to apply effects
   */
  public function getAllBuildingsWithMethod($methodName)
  {
    return self::getInLocation('inPlay')->filter(function ($building) use ($methodName) {
      return \method_exists($building, 'on' . $methodName) ||
        \method_exists($building, 'onPlayer' . $methodName) ||
        \method_exists($building, 'onOpponent' . $methodName);
    });
  }

  public function applyEffects($player, $methodName, &$args)
  {
    // Compute a specific ordering if needed
    $buildings = self::getAllBuildingsWithMethod($methodName)->toAssoc();
    $nodes = array_keys($buildings);
    $edges = [];
    $orderName = 'order' . $methodName;
    foreach ($buildings as $cId => $building) {
      if (\method_exists($building, $orderName)) {
        foreach ($building->$orderName() as $constraint) {
          $cId2 = $constraint[1];
          if (!in_array($cId2, $nodes)) {
            continue;
          }
          $op = $constraint[0];

          // Add the edge
          $edge = [$op == '<' ? $cId : $cId2, $op == '<' ? $cId2 : $cId];
          if (!in_array($edge, $edges)) {
            $edges[] = $edge;
          }
        }
      }
    }
    $topoOrder = Utils::topological_sort($nodes, $edges);
    $orderedBuildings = [];
    foreach ($topoOrder as $cId) {
      $orderedBuildings[] = $buildings[$cId];
    }

    // Apply effects
    $result = false;
    foreach ($orderedBuildings as $building) {
      $res = self::applyEffect($building, $player, $methodName, $args, false);
      $result = $result || $res;
    }
    return $result;
  }

  public function applyEffect($building, $player, $methodName, &$args, $throwErrorIfNone = false)
  {
    $building = $building instanceof \CAV\Models\Building ? $building : self::get($building);
    $res = null;
    $listened = false;
    if (
      $player != null &&
      $player->getId() == $building->getPId() &&
      \method_exists($building, 'onPlayer' . $methodName)
    ) {
      $n = 'onPlayer' . $methodName;
      $res = $building->$n($player, $args);
      $listened = true;
    } elseif (
      $player != null &&
      $player->getId() != $building->getPId() &&
      \method_exists($building, 'onOpponent' . $methodName)
    ) {
      $n = 'onOpponent' . $methodName;
      $res = $building->$n($player, $args);
      $listened = true;
    } elseif (\method_exists($building, 'on' . $methodName)) {
      $n = 'on' . $methodName;
      $res = $building->$n($player, $args);
      $listened = true;
    } elseif ($building->isAnytime($args) && \method_exists($building, 'atAnytime')) {
      $res = $building->atAnytime($player, $args);
      $listened = true;
    }

    if ($throwErrorIfNone && !$listened) {
      throw new \BgaVisibleSystemException(
        'Trying to apply effect of a card without corresponding listener : ' . $methodName . ' ' . $building->getId()
      );
    }

    return $res;
  }

  // /**
  //  * Generate/load seed
  //  */
  // public static function getSeed()
  // {
  //   $res = '';
  //   // TODO
  //   // foreach (Players::getAll() as $player) {
  //   //   $ids = $player
  //   //     ->getHand()
  //   //     ->map(function ($building) {
  //   //       return $building->getDeck() . dechex($building->getNumber());
  //   //     })
  //   //     ->toArray();
  //   //   $res .= ($res != '' ? '|' : '') . implode('', $ids);
  //   // }
  //   return $res;
  // }
  //
  // public static function preSeedClear()
  // {
  //   self::DB()
  //     ->delete()
  //     ->whereNotNull('player_id')
  //     ->run();
  // }
  //
  // public static function setSeed($player, $seed)
  // {
  //   // Extract the list of (deck, number) identifiers
  //   preg_match_all('/([ABCD][0-9a-f]+)/', $seed, $out, PREG_PATTERN_ORDER);
  //   $buildings = [];
  //   foreach ($out[1] as $building) {
  //     $deck = $building[0];
  //     $number = hexdec(\substr($building, 1));
  //     $buildings[] = $deck . $number;
  //   }
  //
  //   // Create the cards
  //   $values = [];
  //   include dirname(__FILE__) . '/../Cards/list.inc.php';
  //   foreach ($buildingIds as $cId) {
  //     $building = self::getCardInstance($cId);
  //     if (in_array($building->getDeck() . $building->getNumber(), $buildings)) {
  //       $values[] = [
  //         'id' => $building->getId(),
  //         'location' => 'hand',
  //         'player_id' => $player->getId(),
  //       ];
  //     }
  //   }
  //   self::create($values, null);
  // }
}
