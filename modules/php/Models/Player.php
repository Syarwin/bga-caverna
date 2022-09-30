<?php
namespace CAV\Models;
use CAV\Managers\Dwarfs;
use CAV\Managers\Actions;
use CAV\Managers\Meeples;
use CAV\Managers\Fences;
use CAV\Managers\Buildings;
use CAV\Core\Engine;
use CAV\Core\Globals;
use CAV\Core\Notifications;
use CAV\Core\Preferences;
use CAV\Actions\Pay;
use CAV\Actions\Reorganize;
use CAV\Helpers\Utils;

/*
 * Player: all utility functions concerning a player
 */

class Player extends \CAV\Helpers\DB_Model
{
  private $board = null;
  protected $table = 'player';
  protected $primary = 'player_id';
  protected $attributes = [
    'id' => ['player_id', 'int'],
    'no' => ['player_no', 'int'],
    'name' => 'player_name',
    'color' => 'player_color',
    'eliminated' => 'player_eliminated',
    'score' => ['player_score', 'int'],
    'scoreAux' => ['player_score_aux', 'int'],
    'zombie' => 'player_zombie',
  ];

  // Cached attribute
  public function board()
  {
    if ($this->board == null) {
      $this->board = new PlayerBoard($this);
    }
    return $this->board;
  }

  public function jsonSerialize($currentPlayerId = null)
  {
    $data = parent::jsonSerialize();
    $current = $this->id == $currentPlayerId;
    $data['harvestCost'] = $this->getHarvestFoodCost();
    $data['dwellingCapacity'] = $this->countDwellings();
    $data['board'] = $this->board()->getUiData();

    return $data;
  }

  public function getPref($prefId)
  {
    return Preferences::get($this->id, $prefId);
  }

  public function getStat($name)
  {
    $name = 'get' . \ucfirst($name);
    return Stats::$name($this->id);
  }

  public function canTakeAction($action, $ctx, $ignoreResources)
  {
    return Actions::isDoable($action, $ctx, $this, $ignoreResources);
  }

  ////////////////////////////////////////////////
  //  ____
  // |  _ \__      ____ _ _ ____   _____  ___
  // | | | \ \ /\ / / _` | '__\ \ / / _ \/ __|
  // | |_| |\ V  V / (_| | |   \ V /  __/\__ \
  // |____/  \_/\_/ \__,_|_|    \_/ \___||___/
  ////////////////////////////////////////////////

  public function getAllDwarfs()
  {
    return Dwarfs::getAllOfPlayer($this->id);
  }

  public function hasDwarfAvailable()
  {
    return Dwarfs::hasAvailable($this->id);
  }

  public function getAvailableDwarfs()
  {
    return Dwarfs::getAllAvailable($this->id);
  }

  public function countDwarfs($type = null)
  {
    return Dwarfs::count($this->id, $type);
  }

  public function hasDwarfInReserve()
  {
    return Dwarfs::hasInReserve($this->id);
  }

  public function hasArmedDwarfs()
  {
    foreach ($this->getAllDwarfs() as $dId => $dwarf) {
      if (($dwarf['weapon'] ?? 0) > 0) {
        return true;
      }
    }
    return false;
  }

  ////////////////////////////////////////////////////
  //  ____        _ _     _ _
  // | __ ) _   _(_) | __| (_)_ __   __ _ ___
  // |  _ \| | | | | |/ _` | | '_ \ / _` / __|
  // | |_) | |_| | | | (_| | | | | | (_| \__ \
  // |____/ \__,_|_|_|\__,_|_|_| |_|\__, |___/
  //                                |___/
  ////////////////////////////////////////////////////

  public function getBuildings($type = null, $playedOnly = false)
  {
    return Buildings::getOfPlayer($this->id)->filter(function ($building) use ($type, $playedOnly) {
      return ($type == null || $building->getType() == $type) && (!$playedOnly || $building->isPlayed());
    });
  }

  public function getPlayedBuildings($type = null)
  {
    return $this->getBuildings($type, true);
  }

  public function hasPlayedBuilding($buildingType, $returnBoolean = true)
  {
    foreach ($this->getPlayedBuildings() as $building) {
      if ($building->getType() == $buildingType) {
        return $returnBoolean? true: $building;
      }
    }

    return $returnBoolean? false : null;
  }

  public function countOreMines()
  {
    return count($this->board()->getTilesOfType(TILE_ORE_MINE));
  }
  public function countRubyMines()
  {
    return count($this->board()->getTilesOfType(TILE_RUBY_MINE));
  }

  public function countDonkeyInMines()
  {
    $totalDonkeys = 0;
    $mines = $this->board()
      ->getTilesOfType(TILE_ORE_MINE)
      ->merge($this->board()->getTilesOfType(TILE_RUBY_MINE));
    $minesCoords = [];
    foreach ($mines as $mId => $mine) {
      $minesCoords[] = ['x' => $mine['x'], 'y' => $mine['y']];
    }
    $donkeys = Meeples::getResourceOfType($this->id, DONKEY);
    foreach ($donkeys as $dId => $donkey) {
      if (in_array(['x' => $donkey['x'], 'y' => $donkey['y']], $minesCoords)) {
        $totalDonkeys++;
      }
    }
    return $totalDonkeys;
  }

  public function countDwellings()
  {
    $buildings = Buildings::getOfPlayer($this->id);
    $dwellings = 0;
    foreach ($buildings as $bId => $building) {
      $dwellings += $building->getDwelling();
    }
    return $dwellings;
  }

  public function countReserveAndGrowingResource($type)
  {
    return $this->countReserveResource($type) +
      $this->board()
        ->getGrowingCrops($type)
        ->count();
  }

  public function countReserveResource($type)
  {
    return Meeples::countReserveResource($this->id, $type);
  }

  public function countAllResource($type)
  {
    return Meeples::countAllResource($this->id, $type);
  }

  public function hasRuby()
  {
    return $this->countReserveResource(RUBY) > 0;
  }

  public function createResourceInReserve($type, $nbr = 1)
  {
    return Meeples::getMany(Meeples::createResourceInReserve($this->id, $type, $nbr))->toArray();
  }

  public function getAllReserveResources()
  {
    $reserve = [];
    foreach (RESOURCES as $res) {
      $reserve[$res] = 0;
    }

    foreach (Meeples::getReserveResource($this->id) as $meeple) {
      if (in_array($meeple['type'], RESOURCES)) {
        $reserve[$meeple['type']]++;
      }
    }

    return $reserve;
  }

  public function getReserveResource($type = null)
  {
    return Meeples::getReserveResource($this->id, $type);
  }

  public function useResource($resource, $amount)
  {
    return Meeples::useResource($this->id, $resource, $amount);
  }

  public function payResourceTo($pId, $resource, $amount)
  {
    return Meeples::payResourceTo($this->id, $resource, $amount, $pId);
  }

  public function getExchangeResources()
  {
    $reserve = $this->getAllReserveResources();
    $reserve = $this->countAnimalsOnBoard($reserve); // Add animals, even if they are not in reserve
    return $reserve;
  }

  public function countStablesInReserve()
  {
    return Meeples::getReserveResource($this->id, 'stable')->count();
  }

  public function getStablesInReserve()
  {
    return Meeples::getReserveResource($this->id, 'stable');
  }

  public function getNextCropToSow($type)
  {
    return Meeples::getReserveResource($this->id, $type)->first();
  }

  public function getPossibleExchanges($trigger = ANYTIME, $removeAnytime = false)
  {
    $exchanges = [
      [
        'from' => [GOLD => 2],
        'to' => [FOOD => 1],
        'triggers' => null,
      ],
      Utils::formatExchange([DONKEY => [FOOD => 1]]),
      Utils::formatExchange([SHEEP => [FOOD => 1]]),
      Utils::formatExchange([GRAIN => [FOOD => 1]]),

      [
        'from' => [GOLD => 3],
        'to' => [FOOD => 2],
        'triggers' => null,
      ],
      Utils::formatExchange([PIG => [FOOD => 2]]),
      Utils::formatExchange([VEGETABLE => [FOOD => 2]]),
      Utils::formatExchange([RUBY => [FOOD => 2]]),

      [
        'from' => [GOLD => 4],
        'to' => [FOOD => 3],
        'triggers' => null,
      ],
      [
        'from' => [DONKEY => 2],
        'to' => [FOOD => 3],
        'triggers' => null,
      ],
      Utils::formatExchange([CATTLE => [FOOD => 3]]),
    ];

    foreach ($this->getPlayedBuildings() as $building) {
      $exchanges = array_merge($exchanges, $building->getExchanges());
    }

    // Filter according to trigger
    Utils::filterExchanges($exchanges, $trigger, $removeAnytime);

    return $exchanges;
  }

  /************************
   ******** ANIMALS ********
   ************************/
  public function checkAutoReorganize(&$meeples)
  {
    return Reorganize::checkAutoReorganize($this, $meeples);
  }

  public function getAnimals($location = null)
  {
    return Meeples::getAnimals($this->id, $location);
  }

  /**
   * Get all animals on board, including the ones on card holders
   */
  public function getAnimalsOnBoard()
  {
    $animals = $this->getAnimals('board');
    foreach ($this->getPlayedBuildings() as $card) {
      if ($card->isAnimalHolder()) {
        $animals = $animals->merge($this->getAnimals($card->getId()));
      }
    }
    return $animals;
  }

  public function getAnimalOnBoard($animal)
  {
    return Meeples::getAnimal($this->id, 'board', $animal);
  }

  public function countAnimalsOnTile($tileType, $animal)
  {
    $animals = 0;
    $tiles = $this->board()->getTilesOfType($tileType);
    foreach ($tiles as $tile) {
      $animals += Meeples::countAnimalInZoneLocation($this->id, $animal, $tile);
    }
    return $animals;
  }

  /**
   * Count the number of animals in a given location
   */
  public function countAnimalsInLocation($location, $res = null)
  {
    $reserve = $res ?? [DOG => 0, SHEEP => 0, PIG => 0, CATTLE => 0, DONKEY => 0];
    foreach (Meeples::getAnimals($this->id, $location) as $meeple) {
      $reserve[$meeple['type']]++;
    }
    return $reserve;
  }

  /**
   * Count the number of animals of each type on board, including on card holders
   */
  public function countAnimalsOnBoard($res = null)
  {
    $res = $this->countAnimalsInLocation('board', $res);
    foreach ($this->getPlayedBuildings() as $building) {
      if ($building->isAnimalHolder()) {
        $res = $this->countAnimalsInLocation($building->getId(), $res);
      }
    }
    return $res;
  }

  public function countAnimalsInReserve($res = null)
  {
    return $this->countAnimalsInLocation('reserve', $res);
  }

  public function countFarmAnimalsInReserve($res = null)
  {
    $reserve = $this->countAnimalsInLocation('reserve', $res);
    unset($reserve[DOG]);
    return $reserve;
  }

  /**
   * Which animals can be converted by a card?
   */
  public function getExchangeableAnimalTypes($trigger = ANYTIME)
  {
    $types = [
      SHEEP => false,
      PIG => false,
      CATTLE => false,
    ];

    // Iterate through all possible exchanges and check "from" field
    foreach ($this->getPossibleExchanges($trigger) as $exchange) {
      foreach ($exchange['from'] as $resType => $amount) {
        if (\array_key_exists($resType, $types)) {
          $types[$resType] = true;
        }
      }
    }

    return $types;
  }

  /**
   * Check whether there are un-accomodated animals in reserve or not, and trigger REORGANIZE if needed
   */
  public function checkAnimalsInReserve($needConfirm = false)
  {
    $animals = $this->countAnimalsInReserve();
    $realAnimals = $animals[SHEEP] + $animals[PIG] + $animals[CATTLE] + $animals[DONKEY];
    if ($needConfirm || $realAnimals > 0) {
      Engine::insertAsChild([
        'action' => REORGANIZE,
      ]);
    }
  }

  /**
   * Check whether some animals are invalid and need reorganize or not
   */
  public function forceReorganizeIfNeeded()
  {
    $animals = $this->board()->getInvalidAnimals(false);
    if (empty($animals)) {
      return;
    }

    // Put all invalid animals in the reserve
    $ids = [];
    foreach ($animals as $meeple) {
      Meeples::moveToCoords($meeple['id'], 'reserve');
      $ids[] = $meeple['id'];
    }

    // Try to accomodate them somewhere else
    $meeples = Meeples::getMany($ids)->toArray();
    $reorganize = $this->checkAutoReorganize($meeples);

    // Notify the deplacements
    Notifications::moveAnimalsAround($this, $meeples);

    // Reorganize if needed
    $this->checkAnimalsInReserve($reorganize);
  }

  /*************************
   ******* PAY SUGAR ********
   *************************/
  public function canPayCost($cost)
  {
    return $this->canPayFee(['fee' => $cost]);
  }

  public function canPayFee($costs)
  {
    return Pay::canPayFee($this, $costs);
  }

  public function canBuy($costs, $n = 1)
  {
    return Pay::canBuy($this, $costs, $n);
  }

  public function maxBuyableAmount($costs)
  {
    return Pay::maxBuyableAmount($this, $costs);
  }

  public function pay($nb, $costs, $source = '', $insertInsideEngine = true)
  {
    $node = [
      'action' => PAY,
      'args' => [
        'nb' => $nb,
        'costs' => $costs,
        'source' => $source,
      ],
    ];
    if ($insertInsideEngine) {
      Engine::insertAsChild($node);
    }
    return $node;
  }

  /**************************
   ********* ACTIONS ********
   **************************/

  public function growFamily($action, $location = 'card')
  {
    $mId = Dwarfs::getNextInReserve($this->id)['id'];
    if ($location == 'card') {
      Meeples::moveToCoords($mId, $action);
    } else {
      Meeples::moveToCoords($mId, 'board', $action);
    }
    Meeples::setState($mId, CHILD); // Tag him as a CHILD
    return Meeples::get($mId);
  }

  public function getDwellings()
  {
    return Buildings::getOfPlayer($this->id)->filter(function ($building) {
      return $building->getDwelling() > 0;
    });
  }

  public function returnHomeDwarfs()
  {
    $dwarfs = $this->getAllDwarfs();
    $rooms = $this->getDwellings()->toArray();
    $roomId = 0;
    $roomFilling = 0;
    foreach ($dwarfs as $dwarf) {
      Meeples::moveToCoords($dwarf['id'], 'board', [$rooms[$roomId]->getX(), $rooms[$roomId]->getY()]);
      $roomFilling++;
      if ($roomFilling >= $rooms[$roomId]->getDwelling()) {
        $roomId++;
        $roomFilling = 0;
      }
    }
  }

  public function getHarvestFoodCost()
  {
    $nb = $this->countDwarfs(ADULT) * Globals::getHarvestCost() + $this->countDwarfs(CHILD);
    if ($this->hasPlayedBuilding('G_MiningCave')) {
      $nb -= $this->countDonkeyInMines();
    }
    return $nb;
  }

  public function getHarvestCost()
  {
    $costs = Utils::formatFee([FOOD => $this->getHarvestFoodCost()]);
    return $costs;
  }

  public function breed($animalType = null, $source = null, $breeds = null)
  {
    $meeples = [];
    $animals = $this->breedTypes();
    $created = [];
    Globals::setBreed([]);

    foreach ($animals as $animal => $value) {
      if ($value == false) {
        continue;
      }
      if ($animalType != null && $animal != $animalType) {
        continue;
      }
      if (!is_null($breeds) && !in_array($animal, $breeds)) {
        continue;
      }

      $created[] = $animal;
      array_push($meeples, ...$this->createResourceInReserve($animal, 1));
    }

    if (count($meeples) > 0) {
      $reorganize = $this->checkAutoReorganize($meeples);
      Globals::setBreed($created);

      if ($this->hasPlayedBuilding('G_Quarry') || $this->hasPlayedBuilding('G_BreedingCave')) {
        $reorganize = true; // force confirm of reorganize
      }
      Notifications::breed($this, $meeples, $source);

      $animals = $this->countAnimalsInReserve();
      $realAnimals = $animals[SHEEP] + $animals[PIG] + $animals[CATTLE] + $animals[DONKEY];
      return $reorganize || $realAnimals > 0;
    }
    return false;
  }

  public function breedTypes()
  {
    $animals = $this->countAnimalsOnBoard();
    $canBreed = [];

    foreach ($animals as $animal => $value) {
      if ($animal == DOG) {
        continue;
      }
      if ($value < 2) {
        $canBreed[$animal] = false;
        continue;
      }
      $canBreed[$animal] = true;
    }

    return $canBreed;
  }

  public function canBreed()
  {
    $breeds = $this->breedTypes();
    foreach ($breeds as $animal => $canBreed) {
      if ($canBreed === true) {
        return true;
      }
    }
    return false;
  }
}
