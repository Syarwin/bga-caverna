<?php
namespace CAV\Models;
use CAV\Managers\Meeples;
use CAV\Managers\Stables;
use CAV\Managers\Players;
use CAV\Managers\Buildings;
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

class PlayerBoard
{
  protected $player = null;
  protected $pId = null;
  protected $grid = []; // grid that holds 'nodes' (room/field/empty) + 'edges' (fences) + virtual intersections
  protected $stablesGrid = []; // same grid for stables
  protected $buildings = [];
  protected $stables = [];
  protected $fields = [];
  protected $pastures = null; // Array of all current pastures
  protected $arePasturesUpToDate = false;
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
    $this->grid = self::createEmptyGrid();
    $this->stablesGrid = self::createEmptyGrid();

    // Separating mountain from the plain
    $this->grid[6][1] = true;
    $this->grid[6][3] = true;
    $this->grid[6][5] = true;
    $this->grid[6][7] = true;

    $this->buildings = Buildings::getbuildings($this->pId)->toArray();
    foreach ($this->buildings as $building) {
      $this->grid[$building->getX()][$building->getY()] = $building;
    }

    // $this->fields = Meeples::getFields($this->pId)->toArray();
    // foreach ($this->fields as &$field) {
    //   $field['uid'] = $field['x'] . '_' . $field['y'];
    //   $this->grid[$field['x']][$field['y']] = $field;
    // }

    $this->stables = Stables::getOnBoard($this->pId)->toArray();
    foreach ($this->stables as $stable) {
      $this->stablesGrid[$stable['x']][$stable['y']] = $stable;
    }
  }

  public function getBuildings()
  {
    return $this->buildings;
  }

  public function getFields()
  {
    return $this->fields;
  }

  /*************************
   ********* ADDERS *********
   *************************/

  /**
   * Add a stable at a given position
   * This only check that the spot is free
   */
  public function addStable(&$stable)
  {
    self::checkNodePos($stable['x'], $stable['y']);

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
   * Add a field at a given position
   * This only check that the spot is free
   */
  public function addField(&$field)
  {
    self::checkNodePos($field['x'], $field['y']);
    if (!$this->isFree($field)) {
      throw new \BgaVisibleSystemException('This node is not free');
    }

    // Create the field meeple and update the variable
    $id = Meeples::createResourceInLocation('field', 'board', $this->pId, $field['x'], $field['y']);
    $field = Meeples::get($id); // Update $field value
    $this->fields[] = $field;
    $this->grid[$field['x']][$field['y']] = $field;
  }

  /**
   * Add a building at a given position
   * This only check that the spot is free
   */
  public function addBuilding($buildingType, &$building)
  {
    self::checkNodePos($building['x'], $building['y']);
    if (!$this->isFree($building)) {
      throw new \BgaVisibleSystemException('This node is not free');
    }

    // Create the building meeple and update the variable
    $id = Buildings::createResourceInLocation($buildingType, 'board', $this->pId, $room['x'], $room['y']);
    $building = Buildings::get($id);
    $this->buildings[] = $building;
    $this->grid[$building->getX()][$building->getY()] = $building;
  }

  /****************************
   ******* SANITY CHECKS *******
   ****************************/

   public function isPlainZone($coord)
   {
     return $coord['x'] < 7;
   }

  public function isMoutainZone($coord)
  {
    return $coord['x'] >= 7;
  }

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
    if (!self::isConnex($marks)) {
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
    $marks = self::getSubgraphMarks($this->getFieldTiles());
    if (!self::isConnex($marks)) {
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
    $marks = self::getSubgraphMarks($this->buildings);
    if (!self::isConnex($marks)) {
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
      if ($zone['type'] == 'card' && method_exists(Buildings::get($zone['card_id']), 'getInvalidAnimals')) {
        $card = Buildings::get($zone['card_id']);
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

  /**************************
   **************************
   ******* ARGS UTILS *******
   **************************
   *************************/

  /**
   * Return all free nodes
   */
  public function getFreeZones($bNotInsidePasture = true, $bForestOnly = true)
  {
    $nodes = self::getAllNodes();

    // Should be free and not in a pasture
    $marks = $this->getPasturesMarks();
    Utils::filter($nodes, function ($pos) use ($marks, $bNotInsidePasture, $bForestOnly) {
      return ($this->isFree($pos) || !$this->containsStable($pos)) &&
        (!$bNotInsidePasture || $marks[$pos['x']][$pos['y']] != INSIDE) &&
        (!$bForestOnly || $pos['x'] < 7);
    });

    return $nodes;
  }

  /**
   * Return all nodes that could receive a specific type, ie free and adjacent to existing same type
   * Used for fields and buildings that share similar constraints
   */
  protected function getAdjacentZones($existingNodes)
  {
    $nodes = $this->getFreeZones();

    // Compute adjacent zones to existing fields
    $adjZones = [];
    foreach ($existingNodes as $pos) {
      if (isset($pos['ignore']) && $pos['ignore'] == true) {
        continue;
      }

      foreach (self::getNodesAround($pos) as $zone) {
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
   * Return all nodes that could receive a field
   */
  public function getPlowableZones()
  {
    // TODO
    return []; //$this->getAdjacentZones($this->getFieldTiles());
  }

  public function canPlow()
  {
    return !empty($this->getPlowableZones());
  }

  /**
   * Return all nodes that could receive a room
   */
  public function getBuildableZones()
  {
    return $this->getAdjacentZones($this->buildings);
  }

  public function canConstruct()
  {
    return !empty($this->getBuildableZones());
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
          'locations' => [self::extractPos($stable)],
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

  /******************************
   ******************************
   ***** FIELDS/CROPS UTILS *****
   ******************************
   *****************************/
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
    foreach ($this->fields as $field) {
      $field['crops'] = [];
      $field['fieldType'] = null;
      $fields[$field['uid']] = $field;
    }

    foreach ($this->getGrowingCrops($keepOnlyThisType) as $crop) {
      $uid = $crop['x'] < 0 || $crop['y'] < 0 ? $crop['location'] : $crop['x'] . '_' . $crop['y'];
      if ($crop['location'] == 'D75_WoodField' && $crop['x'] == 0) {
        $uid = 'D75_WoodField2';
      }
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

  /******************************
   ******************************
   ******* PASTURES UTILS *******
   ******************************
   *****************************/

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
    // foreach (self::getAllNodes() as $pos) {
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
    //       if (!self::isValid($npos) || isset($visited[$npos['x']][$npos['y']])) {
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

  /***********************************
   ***********************************
   *********** GRID UTILS ************
   ***********************************
   ***********************************/
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

  protected static function getAllNodes()
  {
    $result = [];
    for ($x = -1; $x <= 13; $x += 2) {
      for ($y = -1; $y <= 9; $y += 2) {
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
    if ($checkValidity && !self::isValid($x, $y)) {
      throw new \feException('Trying to access a position out of bounds :' . self::posToStr($x, $y));
    }
  }

  protected static function isValid($x, $y = null)
  {
    if ($y === null) {
      $y = $x['y'];
      $x = $x['x'];
    }
    return $x >= -1 && $x <= 13 && $y >= -1 && $y <= 9;
  }

  protected static function checkNodePos(&$x, &$y = null)
  {
    self::checkPos($x, $y);
    if (($x + 2) % 2 != 1 || ($y + 2) % 2 != 1) {
      throw new \feException('Trying to ask node of an edge or a virtual intersection :' . self::posToStr($x, $y));
    }
  }

  public static function extractPos($mixed)
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
    self::checkPos($x, $y);
    return is_null($this->grid[$x][$y]);
  }

  protected function isFreeOrStable($x, $y = null)
  {
    self::checkPos($x, $y);
    return is_null($this->grid[$x][$y]) || $this->containsStable($x, $y);
  }

  /**
   * Util function to test if a stable is present at given position
   */
  protected function containsStable($x, $y = null)
  {
    self::checkNodePos($x, $y);
    return $this->grid[$x][$y] != null &&
      ((is_array($this->grid[$x][$y]) && $this->grid[$x][$y]['type'] == 'stable') ||
        $this->grid[$x][$y]->getType() == 'stable');
  }

  /***********************************
   ***********************************
   *********** DIRS UTILS ************
   ***********************************
   ***********************************/

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
  protected static function nextCellInDirection($pos, $dir, $steps = 1)
  {
    return [
      'x' => $pos['x'] + $steps * self::$dirs[$dir]['x'],
      'y' => $pos['y'] + $steps * self::$dirs[$dir]['y'],
    ];
  }

  /**
   * Return the next 'node' starting from one node and going into a direction
   */
  protected static function nextNodeInDirection($pos, $dir)
  {
    // Going two step into the direction to go over the intersection
    //  since we only care about nodes here
    return self::nextCellInDirection($pos, $dir, 2);
  }

  /**
   * Return an associative array with 4 nodes around a node
   * @param $x,$y  coordinate of 'node'
   */
  protected static function getNodesAround($x, $y = null)
  {
    self::checkNodePos($x, $y);
    $result = [];
    foreach ([W, N, E, S] as $dir) {
      $pos = self::nextNodeInDirection(['x' => $x, 'y' => $y], $dir);
      if (self::isValid($pos)) {
        $result[$dir] = $pos;
      }
    }
    return $result;
  }

  /***********************************
   ***********************************
   ****** GENERIC GRAPH UTILS ********
   ***********************************
   ***********************************/

  /**
   * Create a subgraph of the grids with specified nodes
   */
  protected function getSubgraphMarks($nodes)
  {
    $marks = self::createEmptyGrid();
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

    return self::getSubgraphMarks($nodes);
  }

  /**
   * Test whether a set of nodes is connex or not
   * @param $marks is a grid with null everywhere expect on nodes of the graph we want to test
   */
  protected function isConnex($marks)
  {
    // Search a starting node
    $pos = null;
    foreach (self::getAllNodes() as $node) {
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
        if (!self::isValid($npos) || is_null($marks[$npos['x']][$npos['y']])) {
          continue;
        }

        $marks[$npos['x']][$npos['y']] = null;
        array_push($queue, $npos);
      }
    }

    // Now check whether some pasture nodes were still untouched by the graph exploration
    foreach (self::getAllNodes() as $pos) {
      if ($marks[$pos['x']][$pos['y']] == INSIDE) {
        return false;
      }
    }
    return true;
  }
}
