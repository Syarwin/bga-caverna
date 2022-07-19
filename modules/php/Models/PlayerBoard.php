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
  protected $grid = []; // 10x6 grid that holds 'nodes' (room/field/empty) + 'edges' (fences) + virtual intersections
  protected $fences = [];
  protected $rooms = [];
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

    // Separating mountain from the plain
    $this->grid[6][1] = true;
    $this->grid[6][3] = true;
    $this->grid[6][5] = true;
    $this->grid[6][7] = true;

    // $this->rooms = Meeples::getRooms($this->pId)->toArray();
    // foreach ($this->rooms as $room) {
    //   $this->grid[$room['x']][$room['y']] = $room;
    // }
    //
    // $this->fields = Meeples::getFields($this->pId)->toArray();
    // foreach ($this->fields as &$field) {
    //   $field['uid'] = $field['x'] . '_' . $field['y'];
    //   $this->grid[$field['x']][$field['y']] = $field;
    // }

    // $this->stables = Stables::getOnBoard($this->pId)->toArray();
    // foreach ($this->stables as $stable) {
    //   $this->grid[$stable['x']][$stable['y']] = $stable;
    // }
  }

  public function getRooms()
  {
    return $this->rooms;
  }

  public function getFields()
  {
    return $this->fields;
  }

  /*************************
   ********* ADDERS *********
   *************************/

  /**
   * Add a fence at a given position
   * This only check that the player has a fence in reserve and that the spot is free
   */
  public function addFence(&$fence)
  {
    self::checkFencePos($fence['x'], $fence['y']);

    if (!Fences::hasAvailable($this->pId)) {
      throw new \BgaVisibleSystemException('You do not have any fence available');
    }

    // check fence is on a correct place
    if ($this->containsFence($fence)) {
      throw new \BgaVisibleSystemException('A fence already exist at this position');
    }

    // Move fence
    $id = Fences::moveNextAvailable($this->pId, 'board', $fence);
    $fence = Meeples::get($id);
    $this->fences[] = $fence;
    $this->grid[$fence['x']][$fence['y']] = $fence;
    $this->arePasturesUpToDate = false;
  }

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
   * Add a room at a given position
   * This only check that the spot is free
   */
  public function addRoom($roomType, &$room)
  {
    self::checkNodePos($room['x'], $room['y']);
    if (!$this->isFree($room)) {
      throw new \BgaVisibleSystemException('This node is not free');
    }

    // Create the room meeple and update the variable
    $id = Meeples::createResourceInLocation($roomType, 'board', $this->pId, $room['x'], $room['y']);
    $room = Meeples::get($id);
    $this->rooms[] = $room;
    $this->grid[$room['x']][$room['y']] = $room;
  }

  /**
   * Change all rooms type to $newRoomType
   */
  public function renovateRooms($newRoomType)
  {
    $rooms = $this->rooms;
    $this->rooms = [];
    foreach ($rooms as &$room) {
      $tmp = $room['id'];
      // Remove existing room
      Meeples::DB()->delete($room['id']);
      $this->grid[$room['x']][$room['y']] = null;
      // Add the next room in the same place
      $this->addRoom($newRoomType, $room);
      $room['oldId'] = $tmp;
    }

    return $rooms;
  }

  /****************************
   ******* SANITY CHECKS *******
   ****************************/

  /**
   * Check whether the current board is valid wrt to pastures
   */
  public function arePasturesValid($raiseException = false)
  {
    $pastures = $this->getPastures();
    if (empty($pastures)) {
      return true;
    }

    // Checks that pasture contains nothing expect stable
    foreach ($pastures as $pasture) {
      foreach ($pasture['nodes'] as $pos) {
        if (!$this->isFreeOrStable($pos)) {
          if ($raiseException) {
            throw new UserException(totranslate('A pasture contains a room or a field'));
          }
          return false;
        }
      }
    }

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
   * Check whether the current board is valid wrt to fences
   */
  public function areFencesValid($raiseException = false)
  {
    foreach (self::getAllEdges() as $pos) {
      if ($this->containsFence($pos)) {
        $endpoints = $this->getEndpointConnections($pos);
        if (empty($endpoints['start']) || empty($endpoints['end'])) {
          if ($raiseException) {
            throw new UserException(totranslate('One fence is not connected at one of its endpoint'));
          }

          return false;
        }
      }
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
  public function areRoomsValid($raiseException = false)
  {
    // Check adjacency of fields
    $marks = self::getSubgraphMarks($this->rooms);
    if (!self::isConnex($marks)) {
      if ($raiseException) {
        throw new UserException(totranslate('Some rooms are not adjacent'));
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
  public function getFreeZones($bNotInsidePasture = true)
  {
    $nodes = self::getAllNodes();

    // Should be free and not in a pasture
    $marks = $this->getPasturesMarks();
    Utils::filter($nodes, function ($pos) use ($marks, $bNotInsidePasture) {
      return $this->isFree($pos) && (!$bNotInsidePasture || $marks[$pos['x']][$pos['y']] != INSIDE);
    });

    return $nodes;
  }

  /**
   * Return all nodes that could receive a specific type, ie free and adjacent to existing same type
   * Used for fields and rooms that share similar constraints
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
    return $this->getAdjacentZones($this->getFieldTiles());
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
    return $this->getAdjacentZones($this->rooms);
  }

  public function canConstruct()
  {
    return !empty($this->getBuildableZones());
  }

  /**
   * Return all edges that could receive a fence, ie free and not in-between two buildings
   */
  public function getFencableZones()
  {
    $edges = self::getAllEdges();
    Utils::filter($edges, function ($pos) {
      return !$this->containsFence($pos) && !$this->isSurrounded($pos);
    });
    return $edges;
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

  /**
   * Return all edges that could receive a fence and adjacent to a field
   */
  public function getAvailableFieldFences()
  {
    $availableEdges = [];
    $fencable = $this->getFencableZones();
    foreach ($this->fields as $field) {
      if ($field['type'] == 'fieldCard') {
        continue;
      }

      $edges = self::getEdgesAround($field['x'], $field['y']);
      foreach ($edges as $edge) {
        if (in_array($edge, $fencable)) {
          $availableEdges[] = $edge;
        }
      }
    }

    $availableEdges = array_unique($availableEdges, SORT_REGULAR);
    // throw new \feException(print_r($availableEdges));
    return $availableEdges;
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

    // Add the rooms as a single zone
    $zones[] = [
      'type' => 'room',
      'capacity' => 1,
      'locations' => array_map(['CAV\Models\PlayerBoard', 'extractPos'], $this->rooms),
    ];

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
  public function addFieldCard($id, $details)
  {
    $this->fields[$id] = array_merge($details, [
      'uid' => $id,
      'id' => $id,
      'pId' => $this->pId,
      'location' => $id,
      'type' => 'fieldCard',
      'x' => -1,
      'y' => -1,
    ]);

    // D75_WoodField
    if ($id == 'D75_WoodField') {
      $id .= '2';
      $this->fields[$id] = array_merge($details, [
        'uid' => $id,
        'id' => 'D75_WoodField',
        'pId' => $this->pId,
        'location' => 'D75_WoodField',
        'type' => 'fieldCard',
        'x' => 0,
        'y' => -1,
      ]);
    }
  }

  public function getFieldTiles()
  {
    $fields = $this->fields;
    Utils::filter($fields, function ($field) {
      return $field['type'] == 'field';
    });
    return $fields;
  }

  public function getFieldCards()
  {
    $fields = $this->fields;
    Utils::filter($fields, function ($field) {
      return $field['type'] == 'fieldCard';
    });
    return $fields;
  }

  public function getGrowingCrops($keepOnlyThisType = null)
  {
    $fieldCards = array_map(function ($field) {
      return $field['id'];
    }, $this->getFieldCards());
    $crops = Meeples::getGrowingCrops($this->pId, $fieldCards);

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
      $uid = ($crop['x'] < 0 || $crop['y'] < 0) ? $crop['location'] : $crop['x'] . '_' . $crop['y'];
      if($crop['location'] == 'D75_WoodField' && $crop['x'] == 0){
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
   * WARNING this function presuppose areFencesValid is true
   */
  protected function computePastures()
  {
    $this->pastures = [];
    $visited = [];
    foreach (self::getAllNodes() as $pos) {
      if (isset($visited[$pos['x']][$pos['y']]) || !$this->isFreeOrStable($pos)) {
        continue;
      }
      $fences = $this->getFencesAround($pos);
      if (!isset($fences[W]) || !isset($fences[N])) {
        // The top-left corner of a pasture should have fence on TOP and LEFT
        continue;
      }

      // Run graph exploration
      $queue = [$pos];
      $visited[$pos['x']][$pos['y']] = true;
      $pasture = [
        'nodes' => [],
        'stables' => [],
      ];
      while (!empty($queue)) {
        $pos = array_pop($queue);
        array_push($pasture['nodes'], $pos);
        if ($this->containsStable($pos)) {
          array_push($pasture['stables'], $pos);
        }

        foreach ([W, N, E, S] as $i) {
          if ($this->hasFenceInDirection($pos, $i)) {
            continue;
          }

          $npos = $this->nextNodeInDirection($pos, $i);
          if (!self::isValid($npos) || isset($visited[$npos['x']][$npos['y']])) {
            continue;
          }

          $visited[$npos['x']][$npos['y']] = true;
          array_push($queue, $npos);
        }
      }

      // Check if the pasture is not the outside
      $fenced = true;
      foreach ($pasture['nodes'] as $node) {
        foreach ([W, N, E, S] as $i) {
          if (!$this->hasFenceInDirection($node, $i)) {
            $npos = $this->nextNodeInDirection($node, $i);
            if (!in_array($npos, $pasture['nodes'])) {
              $fenced = false;
              break;
            }
          }
        }
      }

      if ($fenced) {
        array_push($this->pastures, $pasture);
      }
    }
  }

  /**
   * Check wether a player can create a pasture with at most X fences
   * WARNING : this does not ensure the player has enough fences in reserve
   * it's also supposing that current pastures and fences are valid
   * @param int $n : the number of available fences to build a new pasture
   */
  public function canCreateNewPasture($n)
  {
    if ($n == 0) {
      return false;
    }

    $marks = $this->getPasturesMarks();
    $bMustTouchAnotherPasture = !empty($this->getPastures());
    foreach (self::getAllNodes() as $pos) {
      if (!$this->isFreeOrStable($pos)) {
        continue;
      }

      // Is the starting node inside a pasture ?
      $splitPasture = $marks[$pos['x']][$pos['y']] == INSIDE;
      if (($splitPasture || $bMustTouchAnotherPasture) && $this->countFencesAround($pos) == 0) {
        continue; // We cannot create a new pasture in the middle of an existing one, or create a non-adjacent pasture if one already exists
      }

      // Start creating a new pasture starting from $pos
      if ($this->canCreateNewPastureAux($marks, $pos, 0, $n, $splitPasture)) {
        return true;
      }
    }
    return false;
  }

  // Recursive auxiliary function
  protected function canCreateNewPastureAux(&$marks, $pos, $borderSize, $n, $splitPasture)
  {
    // Compute how many fences you need to add the current $pos to work in progress (can be negative !)
    $extraFences = 4 - 2 * $this->countNeighboursAux($pos, $marks) - $this->countFencesAround($pos);
    $borderSize += $extraFences;

    // Do we have anough fences are not in the middle of a pasture ? We happy!
    if ($borderSize <= $n && (!$splitPasture || $this->countFencesAround($pos) != 0)) {
      return true;
    }

    $tmp = $marks[$pos['x']][$pos['y']];
    $marks[$pos['x']][$pos['y']] = CURRENT_WORK;
    foreach ([W, N, E, S] as $dir) {
      if ($this->hasFenceInDirection($pos, $dir)) {
        continue;
      }

      $npos = $this->nextNodeInDirection($pos, $dir);
      if (!self::isValid($npos) || $marks[$npos['x']][$npos['y']] == CURRENT_WORK) {
        continue;
      }

      if ($this->canCreateNewPastureAux($marks, $npos, $borderSize, $n, $splitPasture)) {
        return true;
      }
    }
    $marks[$pos['x']][$pos['y']] = $tmp;
    return false;
  }

  // Auxiliary function to count neighbours in current work recursive function above
  protected function countNeighboursAux(&$pos, &$marks)
  {
    $n = 0;
    foreach ([W, N, E, S] as $dir) {
      $npos = $this->nextNodeInDirection($pos, $dir);
      if (self::isValid($npos) && $marks[$npos['x']][$npos['y']] == CURRENT_WORK) {
        $n++;
      }
    }
    return $n;
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

  protected static function getAllIntersections()
  {
    $result = [];
    for ($x = 0; $x <= 12; $x += 2) {
      for ($y = 0; $y <= 8; $y += 2) {
        $result[] = ['x' => $x, 'y' => $y];
      }
    }
    return $result;
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

  protected static function getAllEdges()
  {
    $result = [];
    for ($x = 0; $x <= 10; $x++) {
      $startingY = $x % 2 == 0 ? 1 : 0;
      for ($y = $startingY; $y <= 6; $y += 2) {
        $result[] = ['x' => $x, 'y' => $y];
      }
    }
    return $result;
  }

  /**
   * Return an associative array of 4 intersections around a node
   * @param $x,$y  coordinate of 'node'
   */
  protected static function getIntersections($x, $y = null)
  {
    self::checkPos($x, $y);
    self::checkNodePos($x, $y);

    $result = [];
    foreach ([NW, NE, SW, SE] as $i) {
      $nx = $x + self::$dirs[$i]['x'];
      $ny = $y + self::$dirs[$i]['y'];
      $result[$i] = ['x' => $nx, 'y' => $ny];
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

  protected static function checkFencePos(&$x, &$y = null)
  {
    self::checkPos($x, $y, false);
    if (($x + 2) % 2 == 1 && ($y + 2) % 2 == 1) {
      throw new \feException('Trying to ask fence of a node :' . self::posToStr($x, $y));
    }

    if (($x + 2) % 2 == 0 && ($y + 2) % 2 == 0) {
      throw new \feException('Trying to ask fence of a virtual intersection :' . self::posToStr($x, $y));
    }
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
   * Util function to test if a fence is present at given position
   */
  protected function containsFence($x, $y = null)
  {
    self::checkFencePos($x, $y);
    return $this->grid[$x][$y] != null;
  }

  /**
   * Util function to test if a stable is present at given position
   */
  protected function containsStable($x, $y = null)
  {
    self::checkNodePos($x, $y);
    return $this->grid[$x][$y] != null && $this->grid[$x][$y]['type'] == 'stable';
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

  /**
   * Return an associative array with 4 edges around a node
   * @param $x,$y  coordinate of 'node'
   */
  protected static function getEdgesAround($x, $y = null)
  {
    self::checkNodePos($x, $y);
    $result = [];
    foreach ([W, N, E, S] as $dir) {
      $result[$dir] = self::nextCellInDirection(['x' => $x, 'y' => $y], $dir);
    }
    return $result;
  }

  /*****************
   *** NON-STATIC ***
   *****************/

  /**
   * Return an associative array with key set only if a fence exists in corresponding direction
   * @param $x,$y  coordinate of 'node'
   */
  protected function getFencesAround($x, $y = null)
  {
    $edges = $this->getEdgesAround($x, $y);
    foreach ([W, N, E, S] as $i) {
      $edge = $edges[$i];
      if (!self::isValid($edge) || is_null($this->grid[$edge['x']][$edge['y']])) {
        unset($edges[$i]);
      }
    }

    return $edges;
  }

  protected function countFencesAround($x, $y = null)
  {
    self::checkNodePos($x, $y);
    return count($this->getFencesAround($x, $y));
  }

  /**
   * Test if a node has a fence in given direction
   * @param $pos : assoc x,y array corresponding to pos
   * @param $dir : direction index (cf self::$dirs)
   */
  protected function hasFenceInDirection($pos, $dir)
  {
    $fences = $this->getFencesAround($pos['x'], $pos['y']);
    return isset($fences[$dir]) && $fences[$dir] != null;
  }

  /**
   * Given a fence position, return true if another fence is connected in given direction
   */
  protected function isConnectedInDirection($fpos, $dir)
  {
    $coeff = in_array($dir, [W, N, E, S]) ? 2 : 1;
    $npos = self::nextCellInDirection($fpos, $dir, $coeff);
    return self::isValid($npos) && $this->containsFence($npos);
  }

  /**
   * Return the set of other fence positions connected (by endpoint) to given fence position
   * @param $fpos : assoc array x,y
   * @return $result : assoc array with start/end which contains the dirs in which there is a fence
   *   start correspond to the start endpoint of a fence (top for a vertical, left for an horizontal)
   * and end correspond to the other endpoint
   */
  protected function getEndpointConnections($x, $y = null)
  {
    self::checkFencePos($x, $y);
    if ($x % 2 == 1) {
      // HORIZONTAL
      $tStart = [W, NW, SW];
      $tEnd = [E, NE, SE];
    } else {
      // VERTICAL
      $tStart = [NW, N, NE];
      $tEnd = [SW, S, SE];
    }

    $fpos = ['x' => $x, 'y' => $y];
    $result = ['start' => [], 'end' => []];
    foreach ($tStart as $dir) {
      if ($this->isConnectedInDirection($fpos, $dir)) {
        $result['start'][] = $dir;
      }
    }
    foreach ($tEnd as $dir) {
      if ($this->isConnectedInDirection($fpos, $dir)) {
        $result['end'][] = $dir;
      }
    }

    return $result;
  }

  /**
   * Return true if an edge is between two non-empty nodes
   * @param $fpos : assoc array x,y
   */
  protected function isSurrounded($x, $y = null)
  {
    self::checkFencePos($x, $y);

    $dirs = $x % 2 == 1 ? [N, S] : [W, E];
    $fpos = ['x' => $x, 'y' => $y];
    foreach ($dirs as $dir) {
      $npos = self::nextCellInDirection($fpos, $dir);
      if (self::isValid($npos) && $this->isFreeOrStable($npos)) {
        return false;
      }
    }

    return true;
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

  /*************************
   ****** CARD D148 ********
   ************************/

  /**
   * Return true if an edge is between two rooms
   * @param $fpos : assoc array x,y
   */
  protected function isSurroundedByRooms($x, $y = null)
  {
    self::checkFencePos($x, $y);

    $dirs = $x % 2 == 1 ? [N, S] : [W, E];
    $fpos = ['x' => $x, 'y' => $y];
    $n = 0;
    foreach ($dirs as $dir) {
      $npos = self::nextCellInDirection($fpos, $dir);
      if (self::isValid($npos)) {
        if (is_null($this->grid[$npos['x']][$npos['y']])) {
          return false;
        }
        if (!in_array($this->grid[$npos['x']][$npos['y']]['type'], ['roomWood', 'roomClay', 'roomStone'])) {
          return false;
        }
        $n++;
      }
    }

    return $n == 2;
  }

  /**
   * Return all edges that could receive a fence, ie free and not in-between two buildings
   */
  public function getSurroundedByRoomsEdges()
  {
    $edges = self::getAllEdges();
    Utils::filter($edges, function ($pos) {
      return $this->isSurroundedByRooms($pos);
    });
    return $edges;
  }
}
