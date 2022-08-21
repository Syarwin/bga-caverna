<?php
namespace CAV\Models;
use CAV\Managers\Meeples;
use CAV\Managers\Stables;
use CAV\Managers\Players;
use CAV\Managers\Buildings;
use CAV\Managers\Tiles;
use CAV\Helpers\UserException;
use CAV\Helpers\Utils;

/*
 * PlayerBoard: all utility functions concerning a player board
 */

define('W', 0);
define('NW', 1);
define('N', 2);
define('NE', 3);
define('E', 4);
define('SE', 5);
define('S', 6);
define('SW', 7);

define('INSIDE', 1);
define('BORDER', 2);
define('CURRENT_WORK', 3);

define('BONUSES', [
  [
    'x' => 5,
    'y' => 1,
    'gain' => [PIG => 1],
  ],
  [
    'x' => 1,
    'y' => 5,
    'gain' => [PIG => 1],
  ],
  [
    'x' => 3,
    'y' => 7,
    'gain' => [FOOD => 1],
  ],
  [
    'x' => 9,
    'y' => 7,
    'gain' => [FOOD => 1],
  ],
  [
    'x' => 11,
    'y' => 1,
    'gain' => [FOOD => 2],
  ],
]);

class PlayerBoard
{
  protected $player = null;
  protected $pId = null;
  protected $grid = []; // grid that holds 'nodes' (room/field/empty) + 'edges' (fences) + virtual intersections
  protected $stablesGrid = []; // same grid for stables
  protected $buildings = [];
  protected $tiles = [];
  protected $stables = [];
  protected $pastures = null; // Array of all current pastures
  protected $arePasturesUpToDate = false;

  private $isExtended = false; // TODO : handle building
  public function __construct($player)
  {
    $this->player = $player;
    $this->pId = $player->getId();
    $this->fetchDatas();
  }

  public function getUiData()
  {
    return [
      'dropZones' => $this->getAnimalsDropZones(),
    ];
  }

  public function refresh()
  {
    $this->fetchDatas();
  }

  /**
   * Fetch DB for fences/rooms/fields and fill the grid
   */
  protected function fetchDatas()
  {
    $this->grid = $this->createEmptyGrid();
    $this->stablesGrid = $this->createEmptyGrid();

    $this->buildings = Buildings::getOfPlayer($this->pId)->toArray();

    $this->tiles = Tiles::getOfPlayer($this->pId);
    foreach ($this->tiles as $tile) {
      $this->grid[$tile['x']][$tile['y']] = $tile;
    }

    $this->stables = Stables::getOnBoard($this->pId)->toArray();
    foreach ($this->stables as $stable) {
      $this->stablesGrid[$stable['x']][$stable['y']] = $stable;
    }
  }

  public function getBuildings()
  {
    return $this->buildings;
  }

  public function getTilesOfType($type)
  {
    return $this->tiles->filter(function ($tile) use ($type) {
      return $tile['type'] == $type;
    });
  }

  public function getFields()
  {
    return $this->getTilesOfType(TILE_FIELD);
  }

  public function getOreMines()
  {
    return $this->getTilesOfType(\TILE_ORE_MINE);
  }

  public function countEmptyCell()
  {
    $nodes = $this->getAllNodes(true);

    // Should be free and of the given type
    Utils::filter($nodes, function ($pos) {
      return is_null($this->grid[$pos['x']][$pos['y']]) && is_null($this->stablesGrid[$pos['x']][$pos['y']]);
    });

    return count($nodes);
  }

  ////////////////////////////////////////
  //     _       _     _
  //    / \   __| | __| | ___ _ __ ___
  //   / _ \ / _` |/ _` |/ _ \ '__/ __|
  //  / ___ \ (_| | (_| |  __/ |  \__ \
  // /_/   \_\__,_|\__,_|\___|_|  |___/
  ////////////////////////////////////////

  /**
   * Add a stable at a given position
   * This only check that the spot is free
   */
  public function addStable(&$stable)
  {
    $this->checkNodePos($stable['x'], $stable['y']);

    if (!Stables::hasAvailable($this->pId)) {
      throw new \BgaVisibleSystemException('You do not have any stable available');
    }

    if (!$this->isFree($stable)) {
      throw new \BgaVisibleSystemException('This node is not free');
    }

    $id = Stables::moveNextAvailable($this->pId, 'board', $stable);
    $stable = Meeples::get($id);
    $this->stables[] = $stable;
    $this->grid[$stable['x']][$stable['y']] = $stable;
    $this->arePasturesUpToDate = false;
  }

  /**
   * Add a tile square at a given position
   */
  public function addTileSquare($tileType, $tileAsset, $pos)
  {
    $this->checkNodePos($pos['x'], $pos['y']);

    // Create the field meeple and update the variable
    $tile = Tiles::createTileOnBoard($tileType, $tileAsset, $this->pId, $pos['x'], $pos['y']);
    $this->tiles[] = $tile;
    // Check bonus under the tile
    $bonus = is_null($this->grid[$pos['x']][$pos['y']]) ? $this->getBonus($pos) : null;
    $this->grid[$pos['x']][$pos['y']] = $tile;
    return [$tile, $bonus];
  }

  public function getBonus($pos)
  {
    foreach (BONUSES as $cell) {
      if ($cell['x'] == $pos['x'] && $cell['y'] == $pos['y']) {
        return $cell['gain'];
      }
    }
    return null;
  }

  /**
   * Add a building at a given position
   * This only check that the spot is free
   */
  public function addBuilding($buildingId, $pos)
  {
    $this->checkNodePos($pos['x'], $pos['y']);

    $building = Buildings::get($buildingId);
    if ($building->getType() == 'D_Dwelling') {
      $building = Buildings::createDwelling();
    }

    $this->buildings[] = $building;
    return $building;
  }

  ///////////////////////////////////////////////////////////////////////
  //  ____              _ _            ____ _               _
  // / ___|  __ _ _ __ (_) |_ _   _   / ___| |__   ___  ___| | _____
  // \___ \ / _` | '_ \| | __| | | | | |   | '_ \ / _ \/ __| |/ / __|
  //  ___) | (_| | | | | | |_| |_| | | |___| | | |  __/ (__|   <\__ \
  // |____/ \__,_|_| |_|_|\__|\__, |  \____|_| |_|\___|\___|_|\_\___/
  //                          |___/
  ///////////////////////////////////////////////////////////////////////

  /**
   * Check whether the current board is valid wrt to pastures
   */
  public function arePasturesValid($raiseException = false)
  {
    $pastures = $this->getPastures();
    if (empty($pastures)) {
      return true;
    }

    // TODO
    // Check adjacency of pastures
    $marks = $this->getPasturesMarks();
    if (!$this->isConnex($marks)) {
      if ($raiseException) {
        throw new UserException(totranslate('Some pastures are not adjacent'));
      }
      return false;
    }

    return true;
  }

  /**
   * Check whether the current board is valid wrt to fields
   */
  public function areFieldsValid($raiseException = false)
  {
    // Check adjacency of fields
    $marks = $this->getSubgraphMarks($this->getFieldTiles());
    if (!$this->isConnex($marks)) {
      if ($raiseException) {
        throw new UserException(totranslate('Some fields are not adjacent'));
      }
      return false;
    }

    return true;
  }

  /**
   * Check whether the current board is valid wrt to rooms
   */
  public function areBuildingsValid($raiseException = false)
  {
    // Check adjacency of fields
    $marks = $this->getSubgraphMarks($this->buildings);
    if (!$this->isConnex($marks)) {
      if ($raiseException) {
        throw new UserException(totranslate('Some buildings are not adjacent'));
      }
      return false;
    }

    return true;
  }

  /**
   * Check whether the current board is valid wrt to animals
   */
  public function areAnimalsValid($raiseException = true)
  {
    $extraAnimals = $this->getInvalidAnimals($raiseException);
    return empty($extraAnimals);
  }

  /**
   * Get animals in an incorrect locations
   */
  public function getInvalidAnimals($raiseException = true)
  {
    $zones = $this->getAnimalsDropZonesWithAnimals(true);
    $animals = [];
    foreach ($zones as $id => $zone) {
      // Add all the unaccomodated animals
      if ($id === 'unaccomodated') {
        foreach ($zone['meeples'] as $meeple) {
          $animals[] = $meeple;
        }
        continue;
      }

      // Check max capacity
      if ($zone['animals'] > $zone['capacity']) {
        if ($raiseException) {
          throw new UserException(totranslate('A room/pasture/stable contains too many animals'));
        }

        // Keep the first one, and return the extra ones
        $i = 0;
        foreach ($zone['meeples'] as $meeple) {
          $i++;
          if ($i > $zone['capacity']) {
            $animals[] = $meeple;
          }
        }
      }

      // Check potential constraints about animal type
      if (isset($zone['constraints'])) {
        foreach ($zone['meeples'] as $meeple) {
          if (!in_array($meeple['type'], $zone['constraints'])) {
            if ($raiseException) {
              throw new UserException(totranslate('This zone cannot contain this type of animal'));
            }

            $animals[] = $meeple;
          }
        }
      }

      // Check card constraint
      if (
        $zone['type'] == 'card' &&
        method_exists(Buildings::getFilteredQuery(null, null, $zone['card_id'])->get(), 'getInvalidAnimals')
      ) {
        $card = Buildings::getFilteredQuery(null, null, $zone['card_id'])->get();
        $animals = array_merge($animals, $card->getInvalidAnimals($zone, $raiseException));
      }
      // Check only one type of animal
      else {
        $type = null;
        foreach ($zone['meeples'] as $meeple) {
          // Take the first meeple as the zone type
          if ($type == null) {
            $type = $meeple['type'];
            continue;
          }

          if ($meeple['type'] != $type) {
            if ($raiseException) {
              throw new UserException(totranslate('A room/pasture/stable contains more than one type of animal'));
            }

            $animals[] = $meeple;
          }
        }
      }
    }

    return $animals;
  }

  /////////////////////////////////////////////////
  //     _                    _   _ _   _ _
  //    / \   _ __ __ _ ___  | | | | |_(_) |___
  //   / _ \ | '__/ _` / __| | | | | __| | / __|
  //  / ___ \| | | (_| \__ \ | |_| | |_| | \__ \
  // /_/   \_\_|  \__, |___/  \___/ \__|_|_|___/
  //              |___/
  /////////////////////////////////////////////////

  /**
   * Return all buildable zones for a building (furnish action)
   */
  // caching buildable zones
  protected $buildableZones = null;
  public function getBuildableZones($building)
  {
    if (is_null($this->buildableZones)) {
      // Compute all caverns (+ tunnel if player played the G_WorkRoom building)
      $this->buildableZones = [];
      foreach ($this->tiles as $tile) {
        if ($tile['type'] == TILE_CAVERN) {
          $this->buildableZones[] = $this->extractPos($tile);
        } elseif (
          $this->player->hasPlayedBuilding('G_WorkRoom') &&
          (tile['type'] == \TILE_TUNNEL || $tile['type'] == \TILE_DEEP_TUNNEL)
        ) {
          $this->buildableZones[] = $this->extractPos($tile);
        }
      }
      // Remove zone with a building already placed on them
      $occupiedZones = array_map(function ($building) {
        return $building->getPos();
      }, $this->buildings);
      $this->buildableZones = Utils::diffZones($this->buildableZones, $occupiedZones);
    }

    $zones = $this->buildableZones;
    if ($building->getType() == 'G_Trader' && $this->player->hasPlayedBuilding('Y_SparePartStorage')) {
      $b = Buildings::getFilteredQuery(null, null, 'Y_SparePartStorage')
        ->get()
        ->first();
      $zones = [['x' => $b->getX(), 'y' => $b->getY()]];
    }

    if ($building->getType() == 'Y_SparePartStorage' && $this->player->hasPlayedBuilding('G_Trader')) {
      $b = Buildings::getFilteredQuery(null, null, 'G_Trader')
        ->get()
        ->first();
      // $zones = ['Y_SparePartStorage' => ['x' => $b->getX(), 'y' => $b->getY()]];
      $zones = [['x' => $b->getX(), 'y' => $b->getY()]];
    }

    return $zones;
  }

  /**
   * Return all fields that could receive a crop
   */
  public function getSowableFields($reserve = null, $ignoreResources = false)
  {
    $reserve = $reserve ?? $this->player->getAllReserveResources();
    $fields = $this->getFieldsAndCrops();
    Utils::filter($fields, function ($field) use ($reserve, $ignoreResources) {
      return empty($field['crops']) &&
        ($ignoreResources || !isset($field['constraints']) || $reserve[$field['constraints']] > 0);
    });
    return $fields;
  }

  /**
   * Return all fields that contain a crop
   */
  public function getPlantedFields()
  {
    $fields = $this->getFieldsAndCrops();
    Utils::filter($fields, function ($field) {
      return !empty($field['crops']);
    });
    return $fields;
  }

  public function canSow($reserve = null, $ignoreResources = false)
  {
    return !empty($this->getSowableFields($reserve, $ignoreResources));
  }

  /**
   * Return all dropzones for animals
   *  a dropzone is described by a total capacity and an array of correspondings cells/pos/cards
   */
  public function getAnimalsDropZones()
  {
    $zones = [];

    // Add the pastures
    foreach ($this->getPastures() as $pasture) {
      $zones[] = [
        'type' => 'pasture',
        'capacity' => 2 ** (count($pasture['stables']) + 1) * count($pasture['nodes']),
        'locations' => $pasture['nodes'],
        'stables' => $pasture['stables'],
      ];
    }

    // // Add the rooms as a single zone
    // $zones[] = [
    //   'type' => 'room',
    //   'capacity' => 1,
    //   'locations' => array_map(['CAV\Models\PlayerBoard', 'extractPos'], $this->rooms),
    // ];

    // Add the unfenced stables
    $marks = $this->getPasturesMarks();
    foreach ($this->stables as $stable) {
      if ($marks[$stable['x']][$stable['y']] == null) {
        $zones[] = [
          'type' => 'stable',
          'capacity' => 1,
          'locations' => [$this->extractPos($stable)],
        ];
      }
    }

    // Apply card effects
    $args['zones'] = $zones;
    Buildings::applyEffects($this->player, 'ComputeDropZones', $args);
    return $args['zones'];
  }

  public function getAnimalsDropZonesWithAnimals($includeUnaccomodatedAnimals = false)
  {
    $zones = $this->getAnimalsDropZones();
    $player = $this->player;
    $animals = $player->getAnimalsOnBoard();

    foreach ($zones as &$zone) {
      $zone[SHEEP] = 0;
      $zone[PIG] = 0;
      $zone[CATTLE] = 0;
      $zone[DOG] = 0;
      $zone[DONKEY] = 0;
      $zone['animals'] = 0;
      $zone['meeples'] = [];

      // Find all animals inside that zone
      $meeples = $animals->toAssoc();
      foreach ($meeples as $i => $animal) {
        $pos = $this->extractPos($animal);
        if (
          ($zone['type'] == 'card' && $zone['card_id'] == $animal['location']) || // CARD HOLDER
          ($zone['type'] != 'card' && in_array($pos, $zone['locations']))
        ) {
          $zone[$animal['type']]++;
          $zone['animals']++;
          $zone['meeples'][] = $animal;
          unset($animals[$i]);
        }
      }
    }

    if ($includeUnaccomodatedAnimals) {
      $zones['unaccomodated']['meeples'] = $animals->toArray();
    }

    return $zones;
  }

  public function countCoveredZonesByPastures()
  {
    $pastures = $this->getPastures();
    $coveredZones = array_reduce(
      $pastures,
      function ($carry, $pasture) {
        return $carry + count($pasture['nodes']);
      },
      0
    );
    return $coveredZones;
  }

  //////////////////////////////
  //  _____ _ _
  // |_   _(_) | ___  ___
  //   | | | | |/ _ \/ __|
  //   | | | | |  __/\__ \
  //   |_| |_|_|\___||___/
  //
  //////////////////////////////

  /**
   * Return all free nodes
   */
  public function getFreeZones($type = null)
  {
    $nodes = $this->getAllNodes();

    // Should be free and of the given type
    Utils::filter($nodes, function ($pos) use ($type) {
      return $this->isFree($pos) &&
        ($type != MOUNTAIN || $this->isMoutainZone($pos)) &&
        ($type != FOREST || $this->isForestZone($pos));
    });

    return $nodes;
  }

  public function getUnbuiltTiles($tileType = null)
  {
    $zones = [];
    foreach ($this->tiles as $tile) {
      if (
        is_null($tileType) ||
        ($tile['type'] == $tileType && $this->grid[$tile['x']][$tile['y']]['type'] == $tileType)
      ) {
        $zones[] = $this->extractPos($tile);
      }
    }
    // Remove zone with a building already placed on them
    $occupiedZones = array_map(function ($building) {
      return $building->getPos();
    }, $this->buildings);
    $zones = Utils::diffZones($zones, $occupiedZones);
    return $zones;
  }

  /**
   * Return all free nodes adjacent to given nodes
   */
  protected function getAdjacentZones($nodes, $existingNodes = null)
  {
    if (is_null($existingNodes)) {
      $existingNodes = $this->tiles;
    }

    // Compute adjacent zones to existing fields
    $adjZones = [];
    foreach ($existingNodes as $pos) {
      if (isset($pos['ignore']) && $pos['ignore'] == true) {
        continue;
      }

      foreach ($this->getNodesAround($pos) as $zone) {
        $adjZones[] = $zone;
      }
    }
    // If non empty, intersect
    if (!empty($adjZones)) {
      $nodes = Utils::intersectZones($nodes, $adjZones);
    }

    return $nodes;
  }

  /**
   * Return all nodes that could receive a single tile
   */
  public function getPlacableZones($tile, $checkAdjacency = true)
  {
    $nodes = [];
    if (in_array($tile, [TILE_FIELD, TILE_MEADOW])) {
      $nodes = $this->getFreeZones(FOREST);
    } elseif (in_array($tile, [TILE_CAVERN, TILE_TUNNEL])) {
      $nodes = $this->getFreeZones(MOUNTAIN);
    } elseif (in_array($tile, [TILE_DEEP_TUNNEL, TILE_ORE_MINE])) {
      $nodes = $this->getUnbuiltTiles(TILE_TUNNEL);
    } elseif (in_array($tile, [TILE_PASTURE])) {
      $nodes = $this->getUnbuiltTiles(TILE_MEADOW);
    } else {
      return [];
      die('TODO : getPlacableZones : ' . $tile);
    }

    return $checkAdjacency ? $this->getAdjacentZones($nodes) : $nodes;
  }

  /**
   * Return all placement option for a tile
   */
  public function getPlacementOptions($tile)
  {
    // Decompose any twin tile into two squares
    $tiles = TILE_SQUARES_MAPPING[$tile];

    // Get buildable zone
    $zones = [];
    if (count($tiles) == 1) {
      foreach ($this->getPlacableZones($tiles[0]) as $pos) {
        $zones[] = [
          'pos1' => $pos,
        ];
      }
    } elseif (count($tiles) == 2) {
      $zones = [];
      for ($i = 0; $i <= 1; $i++) {
        foreach ($this->getPlacableZones($tiles[$i]) as $pos) {
          $neighbours = $this->getAdjacentZones($this->getPlacableZones($tiles[1 - $i], false), [$pos]);
          foreach ($neighbours as $pos2) {
            $zones[] = [
              'pos1' => $i == 0 ? $pos : $pos2,
              'pos2' => $i == 0 ? $pos2 : $pos,
            ];
          }
        }
      }
    } else {
      return [];
      die('TODO : getPlacementOptions');
    }

    return $zones;
  }

  public function canPlace($tiles)
  {
    foreach ($tiles as $tile) {
      if (!empty($this->getPlacementOptions($tile))) {
        return true;
      }
    }
    return false;
  }

  /**
   * Add a tile at a given position(s)
   */
  public function addTile($tile, $positions)
  {
    $tiles = TILE_SQUARES_MAPPING[$tile];

    // Get buildable zone
    if (count($tiles) == 1) {
      die('TODO');
    } elseif (count($tiles) == 2) {
      // Compute rotation
      $rotation = 0;
      $dx = $positions[0]['x'] - $positions[1]['x'];
      $dy = $positions[0]['y'] - $positions[1]['y'];
      // Left/right
      if ($dy == 0) {
        $rotation = $dx > 0 ? 2 : 0;
      } else {
        $rotation = $dy > 0 ? 1 : 3;
      }

      $squares = [];
      $bonus = null;
      for ($i = 0; $i <= 1; $i++) {
        $tileAsset = $tile . '-' . $i . '_' . $rotation;
        list($square, $coveredBonus) = $this->addTileSquare($tiles[$i], $tileAsset, $positions[$i]);
        $squares[] = $square;
        $bonus = $bonus ?? $coveredBonus;
      }
      return [$squares, $bonus];
    }
  }

  ///////////////////////////////////////////////////////////////////
  //  _____ _      _     _        ______
  // |  ___(_) ___| | __| |___   / / ___|_ __ ___  _ __  ___
  // | |_  | |/ _ \ |/ _` / __| / / |   | '__/ _ \| '_ \/ __|
  // |  _| | |  __/ | (_| \__ \/ /| |___| | | (_) | |_) \__ \
  // |_|   |_|\___|_|\__,_|___/_/  \____|_|  \___/| .__/|___/
  //                                              |_|
  ///////////////////////////////////////////////////////////////////

  public function getGrowingCrops($keepOnlyThisType = null)
  {
    $crops = Meeples::getGrowingCrops($this->pId);

    if ($keepOnlyThisType != null) {
      $crops = $crops->filter(function ($crop) use ($keepOnlyThisType) {
        return $crop['type'] == $keepOnlyThisType;
      });
    }

    return $crops;
  }

  public function getFieldsAndCrops($keepOnlyThisType = null)
  {
    $fields = [];
    foreach ($this->getFields() as $field) {
      $field['uid'] = $field['x'] . '_' . $field['y'];
      $field['crops'] = [];
      $field['fieldType'] = null;
      $fields[$field['uid']] = $field;
    }

    foreach ($this->getGrowingCrops($keepOnlyThisType) as $crop) {
      $uid = $crop['x'] < 0 || $crop['y'] < 0 ? $crop['location'] : $crop['x'] . '_' . $crop['y'];
      $fields[$uid]['crops'][] = $crop;
      $fields[$uid]['fieldType'] = $crop['type'];
    }

    if ($keepOnlyThisType != null) {
      Utils::filter($fields, function ($field) use ($keepOnlyThisType) {
        return $field['fieldType'] == $keepOnlyThisType;
      });
    }

    return $fields;
  }

  public function getHarvestCrops()
  {
    $fields = $this->getFieldsAndCrops();
    $crops = [];
    foreach ($fields as $field) {
      $fcrops = $field['crops'];
      if (!empty($fcrops)) {
        $crops[] = $fcrops[count($fcrops) - 1];
      }
    }
    return $crops;
  }

  public function getGrainFields()
  {
    return $this->getFieldsAndCrops(GRAIN);
  }

  public function getVegetableFields()
  {
    return $this->getFieldsAndCrops(VEGETABLE);
  }

  //////////////////////////////////////////////////////////////////
  //  ____           _                         _   _ _   _ _
  // |  _ \ __ _ ___| |_ _   _ _ __ ___  ___  | | | | |_(_) |___
  // | |_) / _` / __| __| | | | '__/ _ \/ __| | | | | __| | / __|
  // |  __/ (_| \__ \ |_| |_| | | |  __/\__ \ | |_| | |_| | \__ \
  // |_|   \__,_|___/\__|\__,_|_|  \___||___/  \___/ \__|_|_|___/
  //
  //////////////////////////////////////////////////////////////////

  /**
   * Public function to get the list of all pastures
   * @param $forceRecomputation : boolean value to avoid caching
   */
  public function getPastures($forceRecomputation = false)
  {
    if ($this->pastures == null || $forceRecomputation || !$this->arePasturesUpToDate) {
      $this->computePastures();
      $this->arePasturesUpToDate = true;
    }
    return $this->pastures;
  }

  /**
   * Compute and store the set of all pastures
   */
  protected function computePastures()
  {
    // TODO
    $this->pastures = [];
    // $visited = [];
    // foreach ($this->getAllNodes() as $pos) {
    //   if (isset($visited[$pos['x']][$pos['y']]) || !$this->isFreeOrStable($pos)) {
    //     continue;
    //   }
    //   $fences = $this->getFencesAround($pos);
    //   if (!isset($fences[W]) || !isset($fences[N])) {
    //     // The top-left corner of a pasture should have fence on TOP and LEFT
    //     continue;
    //   }
    //
    //   // Run graph exploration
    //   $queue = [$pos];
    //   $visited[$pos['x']][$pos['y']] = true;
    //   $pasture = [
    //     'nodes' => [],
    //     'stables' => [],
    //   ];
    //   while (!empty($queue)) {
    //     $pos = array_pop($queue);
    //     array_push($pasture['nodes'], $pos);
    //     if ($this->containsStable($pos)) {
    //       array_push($pasture['stables'], $pos);
    //     }
    //
    //     foreach ([W, N, E, S] as $i) {
    //       if ($this->hasFenceInDirection($pos, $i)) {
    //         continue;
    //       }
    //
    //       $npos = $this->nextNodeInDirection($pos, $i);
    //       if (!$this->isValid($npos) || isset($visited[$npos['x']][$npos['y']])) {
    //         continue;
    //       }
    //
    //       $visited[$npos['x']][$npos['y']] = true;
    //       array_push($queue, $npos);
    //     }
    //   }
    //
    //   // Check if the pasture is not the outside
    //   $fenced = true;
    //   foreach ($pasture['nodes'] as $node) {
    //     foreach ([W, N, E, S] as $i) {
    //       if (!$this->hasFenceInDirection($node, $i)) {
    //         $npos = $this->nextNodeInDirection($node, $i);
    //         if (!in_array($npos, $pasture['nodes'])) {
    //           $fenced = false;
    //           break;
    //         }
    //       }
    //     }
    //   }
    //
    //   if ($fenced) {
    //     array_push($this->pastures, $pasture);
    //   }
    // }
  }

  /**
   * Check wether a player can create a pasture with at most X fences
   */
  public function canCreateNewPasture()
  {
    // TODO
    return false;
  }

  /////////////////////////////////////////////////
  //   ____      _     _   _   _ _   _ _
  //  / ___|_ __(_) __| | | | | | |_(_) |___
  // | |  _| '__| |/ _` | | | | | __| | / __|
  // | |_| | |  | | (_| | | |_| | |_| | \__ \
  //  \____|_|  |_|\__,_|  \___/ \__|_|_|___/
  //
  /////////////////////////////////////////////////

  public static function isForestZone($coord)
  {
    return $coord['x'] <= 5;
  }

  public static function isMoutainZone($coord)
  {
    return $coord['x'] >= 7;
  }

  protected static function createEmptyGrid()
  {
    $t = [];
    for ($x = -1; $x <= 13; $x++) {
      for ($y = -1; $y <= 9; $y++) {
        $t[$x][$y] = null;
      }
    }
    return $t;
  }

  protected static function getAllNodes($smallOnly = false)
  {
    $result = [];
    for ($x = $smallOnly ? 1 : -1; $x <= ($smallOnly ? 11 : 13); $x += 2) {
      for ($y = $smallOnly ? 1 : -1; $y <= ($smallOnly ? 7 : 9); $y += 2) {
        $result[] = ['x' => $x, 'y' => $y];
      }
    }
    return $result;
  }

  /**
   * Sanity checks
   */
  protected function posToStr($x, $y)
  {
    return '(' . $x . ',' . $y . ')';
  }
  protected function checkPos(&$x, &$y, $checkValidity = true)
  {
    if ($y === null) {
      $y = $x['y'];
      $x = $x['x'];
    }
    if ($checkValidity && !$this->isValid($x, $y, true)) {
      throw new \feException('Trying to access a position out of bounds :' . $this->posToStr($x, $y));
    }
  }

  protected function isValid($x, $y = null, $checkExtented = false)
  {
    if ($y === null) {
      $y = $x['y'];
      $x = $x['x'];
    }
    if ($this->isExtended || $checkExtented) {
      return $x >= -1 && $x <= 13 && $y >= -1 && $y <= 9;
    } else {
      return $x >= 0 && $x <= 12 && $y >= 0 && $y <= 8;
    }
  }

  protected function checkNodePos(&$x, &$y = null)
  {
    $this->checkPos($x, $y);
    if (($x + 2) % 2 != 1 || ($y + 2) % 2 != 1) {
      throw new \feException('Trying to ask node of an edge or a virtual intersection :' . $this->posToStr($x, $y));
    }
  }

  public function extractPos($mixed)
  {
    $r = [
      'x' => $mixed['x'],
      'y' => $mixed['y'],
    ];
    return $r;
  }

  /*****************
   *** NON-STATIC ***
   *****************/
  protected function isFree($x, $y = null)
  {
    $this->checkPos($x, $y);
    return is_null($this->grid[$x][$y]);
  }

  /**
   * Util function to test if a stable is present at given position
   */
  protected function containsStable($x, $y = null)
  {
    $this->checkNodePos($x, $y);
    return $this->stablesGrid[$x][$y] != null;
  }

  /////////////////////////////////////////////
  //  ____  _            _   _ _   _ _
  // |  _ \(_)_ __ ___  | | | | |_(_) |___
  // | | | | | '__/ __| | | | | __| | / __|
  // | |_| | | |  \__ \ | |_| | |_| | \__ \
  // |____/|_|_|  |___/  \___/ \__|_|_|___/
  //
  /////////////////////////////////////////////

  // The 8 directions
  protected static $dirs = [
    W => ['x' => -1, 'y' => 0],
    NW => ['x' => -1, 'y' => -1],
    N => ['x' => 0, 'y' => -1],
    NE => ['x' => 1, 'y' => -1],
    E => ['x' => 1, 'y' => 0],
    SE => ['x' => 1, 'y' => 1],
    S => ['x' => 0, 'y' => 1],
    SW => ['x' => -1, 'y' => 1],
  ];

  // The two intersections corresponding to a direction
  protected static $intersectionDirs = [
    N => [NW, NE],
    E => [NE, SE],
    S => [SE, SW],
    W => [NW, SW],
  ];

  /**
   * Return the next cell starting from one cell
   */
  protected function nextCellInDirection($pos, $dir, $steps = 1)
  {
    return [
      'x' => $pos['x'] + $steps * self::$dirs[$dir]['x'],
      'y' => $pos['y'] + $steps * self::$dirs[$dir]['y'],
    ];
  }

  /**
   * Return the next 'node' starting from one node and going into a direction
   */
  protected function nextNodeInDirection($pos, $dir)
  {
    // Going two step into the direction to go over the intersection
    //  since we only care about nodes here
    return $this->nextCellInDirection($pos, $dir, 2);
  }

  /**
   * Return an associative array with 4 nodes around a node
   * @param $x,$y  coordinate of 'node'
   */
  protected function getNodesAround($x, $y = null)
  {
    $this->checkNodePos($x, $y);
    $result = [];
    foreach ([W, N, E, S] as $dir) {
      $pos = $this->nextNodeInDirection(['x' => $x, 'y' => $y], $dir);
      if ($this->isValid($pos)) {
        // Consider nodes on the left and right of the middle border not-adjacent (unless it's bottom row)
        if (in_array($dir, [W, E]) && $y != 7 && abs($x - 6) <= 1 && abs($pos['x'] - 6) <= 1) {
          continue;
        }

        $result[$dir] = $pos;
      }
    }
    return $result;
  }

  ///////////////////////////////////////////////////////////
  //   ____                 _       _   _ _   _ _
  //  / ___|_ __ __ _ _ __ | |__   | | | | |_(_) |___
  // | |  _| '__/ _` | '_ \| '_ \  | | | | __| | / __|
  // | |_| | | | (_| | |_) | | | | | |_| | |_| | \__ \
  //  \____|_|  \__,_| .__/|_| |_|  \___/ \__|_|_|___/
  //                 |_|
  ///////////////////////////////////////////////////////////

  /**
   * Create a subgraph of the grids with specified nodes
   */
  protected function getSubgraphMarks($nodes)
  {
    $marks = $this->createEmptyGrid();
    foreach ($nodes as $pos) {
      $marks[$pos['x']][$pos['y']] = INSIDE;
    }

    return $marks;
  }

  /**
   * Create a subgraph of the grids with nodes inside pastures
   * @param $pastures : array of pasture => ['nodes' => array of node, 'stables' => array of stables]
   */
  public function getPasturesMarks($pastures = null)
  {
    if ($pastures === null) {
      $pastures = $this->getPastures();
    }

    $nodes = [];
    foreach ($pastures as $pasture) {
      foreach ($pasture['nodes'] as $pos) {
        $nodes[] = $pos;
      }
    }

    return $this->getSubgraphMarks($nodes);
  }

  /**
   * Test whether a set of nodes is connex or not
   * @param $marks is a grid with null everywhere expect on nodes of the graph we want to test
   */
  protected function isConnex($marks)
  {
    // Search a starting node
    $pos = null;
    foreach ($this->getAllNodes() as $node) {
      if (!is_null($marks[$node['x']][$node['y']])) {
        $pos = $node;
        break;
      }
    }

    if ($pos == null) {
      // If no node, we consider it's connex
      return true;
    }

    // Run a graph exploration from this node
    $queue = [$pos];
    $marks[$pos['x']][$pos['y']] = null;
    while (!empty($queue)) {
      $pos = array_pop($queue);
      foreach ([W, N, E, S] as $i) {
        $npos = $this->nextNodeInDirection($pos, $i);
        if (!$this->isValid($npos) || is_null($marks[$npos['x']][$npos['y']])) {
          continue;
        }

        $marks[$npos['x']][$npos['y']] = null;
        array_push($queue, $npos);
      }
    }

    // Now check whether some pasture nodes were still untouched by the graph exploration
    foreach ($this->getAllNodes() as $pos) {
      if ($marks[$pos['x']][$pos['y']] == INSIDE) {
        return false;
      }
    }
    return true;
  }
}
