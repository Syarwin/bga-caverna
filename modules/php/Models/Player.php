<?php
namespace CAV\Models;
use CAV\Managers\Dwarves;
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

  public function jsonSerialize($currentPlayerId = null)
  {
    $data = parent::jsonSerialize();
    $current = $this->id == $currentPlayerId;
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




  // UNCHECKED
  // public function jsonSerialize($currentPlayerId = null)
  // {
  //   $current = $this->id == $currentPlayerId;
  //   $data = [
  //     'id' => $this->id,
  //     'eliminated' => $this->eliminated,
  //     'no' => $this->no,
  //     'name' => $this->getName(),
  //     'color' => $this->color,
  //     'score' => $this->score,
  //     'resources' => [],
  //     'board' => $this->board()->getUiData(),
  //     'hand' => $current ? $this->getHand()->ui() : [],
  //     'harvestCost' => $this->getHarvestCost(),
  //   ];
  //
  //   foreach (RESOURCES as $resource) {
  //     $data['resources'][$resource] = $this->countReserveResource($resource);
  //   }
  //
  //   return $data;
  // }

  public function board()
  {
    if ($this->board == null) {
      $this->board = new PlayerBoard($this);
    }
    return $this->board;
  }

  public function countRooms()
  {
    return Meeples::countRooms($this->id);
  }

  public function canTakeAction($action, $ctx, $ignoreResources)
  {
    return Actions::isDoable($action, $ctx, $this, $ignoreResources);
  }

  public function countReserveResource($type)
  {
    return Meeples::countReserveResource($this->id, $type);
  }

  public function countAllResource($type)
  {
    return Meeples::countAllResource($this->id, $type);
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

  public function getExchangeResources()
  {
    $reserve = $this->getAllReserveResources();
    $reserve = $this->countAnimalsOnBoard($reserve); // Add animals, even if they are not in reserve
    return $reserve;
  }

  public function getRoomType()
  {
    return Meeples::getRoomType($this->id);
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

  public function countOccupations()
  {
    return $this->getCards(OCCUPATION, true)->count();
  }

  public function countAllImprovements()
  {
    return $this->getCards(MAJOR, true)->count() + $this->getCards(MINOR, true)->count();
  }

  public function getCards($type = null, $playedOnly = false)
  {
    return Buildings::getOfPlayer($this->id)->filter(function ($card) use ($type, $playedOnly) {
      return ($type == null || $card->getType() == $type) && (!$playedOnly || $card->isPlayed());
    });
  }

  public function getDraftSelection()
  {
    return $this->getCards()->filter(function ($card) {
      return $card->getLocation() == 'selection';
    });
  }

  public function getHand($type = null)
  {
    return Buildings::getOfPlayer($this->id)->filter(function ($card) use ($type) {
      return ($type == null || $card->getType() == $type) && !$card->isPlayed();
    });
  }

  public function getPlayedCards($type = null)
  {
    return $this->getCards($type, true);
  }

  public function getActionCards()
  {
    return $this->getPlayedCards()->filter(function ($card) {
      return $card->isActionCard();
    });
  }

  public function hasPlayedCard($cardId)
  {
    return $this->getPlayedCards()->reduce(function ($carry, $card) use ($cardId) {
      return $carry || $card->getId() == $cardId;
    }, false);
  }

  public function canCook()
  {
    return $this->getPlayedCards()->reduce(function ($carry, $card) {
      return $carry || $card->canCook();
    }, false);
  }

  public function canBake()
  {
    return $this->getPlayedCards()->reduce(function ($carry, $card) {
      return $carry || $card->canBake();
    }, false);
  }

  public function getPossibleExchanges($trigger = ANYTIME, $removeAnytime = false)
  {
    $exchanges = [Utils::formatExchange([GRAIN => [FOOD => 1]]), Utils::formatExchange([VEGETABLE => [FOOD => 1]])];

    foreach ($this->getPlayedCards() as $card) {
      $exchanges = array_merge($exchanges, $card->getExchanges());
    }

    // Filter according to trigger
    Utils::filterExchanges($exchanges, $trigger, $removeAnytime);

    return $exchanges;
  }

  public function useResource($resource, $amount)
  {
    return Meeples::useResource($this->id, $resource, $amount);
  }

  public function payResourceTo($pId, $resource, $amount)
  {
    return Meeples::payResourceTo($this->id, $resource, $amount, $pId);
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
    foreach ($this->getPlayedCards() as $card) {
      if ($card->isAnimalHolder()) {
        $animals = $animals->merge($this->getAnimals($card->getId()));
      }
    }
    return $animals;
  }

  /**
   * Count the number of animals in a given location
   */
  public function countAnimalsInLocation($location, $res = null)
  {
    $reserve = $res ?? [SHEEP => 0, PIG => 0, CATTLE => 0];
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
    foreach ($this->getPlayedCards() as $card) {
      if ($card->isAnimalHolder()) {
        $res = $this->countAnimalsInLocation($card->getId(), $res);
      }
    }
    return $res;
  }

  public function countAnimalsInReserve($res = null)
  {
    return $this->countAnimalsInLocation('reserve', $res);
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
    if ($needConfirm || $this->getAnimals('reserve')->count() > 0) {
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
   ****** FARMERS SUGAR ******
   **************************/
  public function getAllDwarves()
  {
    return Dwarves::getAllOfPlayer($this->id);
  }

  public function hasFarmerAvailable()
  {
    return Dwarves::hasAvailable($this->id);
  }

  public function hasAdoptiveAvailable()
  {
    return $this->hasPlayedCard('A92_AdoptiveParents') &&
      Dwarves::hasChildren($this->id) &&
      !in_array($this->id, Globals::getSkippedPlayers());
  }

  // TODO : useful ??
  public function getNextFarmerAvailable()
  {
    return Dwarves::getNextAvailable($this->id);
  }

  public function moveNextFarmerAvailable($location, $coords = null)
  {
    return Dwarves::moveNextAvailable($this->id, $location, $coords);
  }

  public function countDwarves($type = null)
  {
    return Dwarves::count($this->id, $type);
  }

  public function hasFarmerInReserve()
  {
    return Dwarves::hasInReserve($this->id);
  }

  /**************************
   ********* ACTIONS ********
   **************************/

  public function growFamily($action, $location = 'card')
  {
    $mId = Dwarves::getNextInReserve($this->id)['id'];
    if ($location == 'card') {
      Meeples::moveToCoords($mId, $action);
    } else {
      Meeples::moveToCoords($mId, 'board', $action);
    }
    Meeples::setState($mId, CHILD); // Tag him as a CHILD
    return Meeples::get($mId);
  }

  public function returnHomeOne($fId)
  {
    $rooms = Meeples::getRooms($this->id);
    $room = $rooms->first();
    return Meeples::moveToCoords($fId, 'board', [$room['x'], $room['y']]);
  }

  public function getFreeRoom()
  {
    $rooms = Meeples::getRooms($this->id);
    foreach ($rooms as $room) {
      if ($this->countFarmerAtPos(['x' => $room['x'], 'y' => $room['y']]) == 0) {
        return $room;
      }
    }
    return null;
  }

  public function countFarmerAtPos($coord)
  {
    return Meeples::getOnCardQ('board', $this->id)
      ->where('type', 'farmer')
      ->where('x', $coord['x'])
      ->where('y', $coord['y'])
      ->count();
  }

  public function returnHomeDwarves()
  {
    $farmers = self::getAllDwarves();
    $rooms = Meeples::getRooms($this->id);
    $caravan = false;
    if ($this->hasPlayedCard('B10_Caravan')) {
      $caravan = true;
    }

    foreach ($farmers as $farmer) {
      if ($farmer['location'] == 'board' || $farmer['location'] == 'B10_Caravan') {
        continue;
      }

      foreach ($rooms as $room) {
        if ($this->countFarmerAtPos(['x' => $room['x'], 'y' => $room['y']]) == 0) {
          Meeples::moveToCoords($farmer['id'], 'board', [$room['x'], $room['y']]);
          continue;
        }
      }
    }

    // check if all farmers have been allocated. else we put in an existing room as they may have been born from urgent wish for children
    foreach (self::getAllDwarves() as $farmer) {
      if ($farmer['location'] == 'board' || $farmer['location'] == 'B10_Caravan') {
        continue;
      }

      if ($caravan) {
        Meeples::moveToCoords($farmer['id'], 'B10_Caravan');
        $caravan = false;
        continue;
      }

      foreach ($rooms as $room) {
        if ($this->countFarmerAtPos(['x' => $room['x'], 'y' => $room['y']]) == 1) {
          Meeples::moveToCoords($farmer['id'], 'board', [$room['x'], $room['y']]);
          continue 2;
        }
      }

      foreach ($rooms as $room) {
        if ($this->countFarmerAtPos(['x' => $room['x'], 'y' => $room['y']]) == 2) {
          Meeples::moveToCoords($farmer['id'], 'board', [$room['x'], $room['y']]);
          continue;
        }
      }
    }
  }

  public function getHarvestCost()
  {
    $mult = Globals::isSolo() ? 3 : 2;
    return $this->countDwarves(ADULT) * $mult + $this->countDwarves(CHILD);
  }

  public function breed($animalType = null, $source = null)
  {
    $meeples = [];
    $animals = $this->breedTypes();
    $created = [];
    Globals::setD115([]);

    foreach ($animals as $animal => $value) {
      if ($value == false) {
        continue;
      }
      if ($animalType != null && $animal != $animalType) {
        continue;
      }

      $created[] = $animalType;
      array_push($meeples, ...$this->createResourceInReserve($animal, 1));
    }

    if (count($meeples) > 0) {
      $reorganize = $this->checkAutoReorganize($meeples);
      if ($this->hasPlayedCard('D115_FodderPlanter')) {
        Globals::setD115($created);
        $reorganize = true; // force confirm of reorganize
      }
      Notifications::breed($this, $meeples, $source);
      return $reorganize || $this->getAnimals('reserve')->count() > 0;
    }
    return false;
  }

  public function breedTypes()
  {
    $animals = $this->countAnimalsOnBoard();
    $canBreed = [];

    foreach ($animals as $animal => $value) {
      if ($value < 2) {
        $canBreed[$animal] = false;
        continue;
      }
      $canBreed[$animal] = true;
    }

    return $canBreed;
  }

  /***************************
   ******* CARD SPECIFIC ******
   ***************************/
  public function updateObtainedResources($meeples)
  {
    $g = Globals::getObtainedResourcesDuringWork();
    if (!isset($g[$this->id])) {
      $g[$this->id] = [];
    }

    foreach ($meeples as $meeple) {
      $g[$this->id][$meeple['type']] = ($g[$this->id][$meeple['type']] ?? 0) + 1;
    }

    Globals::setObtainedResourcesDuringWork($g);
  }
}
