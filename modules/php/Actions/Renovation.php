<?php
namespace CAV\Actions;

use CAV\Managers\Players;
use CAV\Managers\Meeples;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Helpers\UserException;

class Renovation extends \CAV\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = clienttranslate('Renovate');
  }

  public $renovationCosts = [
    'roomClay' => ['fees' => [[REED => 1]], 'trades' => [[CLAY => 1]]],
    'roomStone' => ['fees' => [[REED => 1]], 'trades' => [[STONE => 1]]],
  ];

  public $renovationLink = ['roomWood' => 'roomClay', 'roomClay' => 'roomStone', 'roomStone' => null];

  public function getState()
  {
    return ST_RENOVATION;
  }

  public function getCosts($player, $newRoomType = null)
  {
    $roomType = $player->getRoomType();

    if ($newRoomType == null) {
      $newRoomType = $this->renovationLink[$roomType];
    }

    $costs = $this->getCtxArgs()['costs'] ?? $this->renovationCosts[$newRoomType];

    $eventData = [
      'oldRoomType' => $roomType,
      'newRoomType' => $newRoomType,
    ];

    $this->checkCostModifiers($costs, $player, $eventData);
    return $costs;
  }

  public function isDoable($player, $ignoreResources = false)
  {
    $roomType = $player->getRoomType();
    if ($roomType == 'roomStone') {
      return false;
    }

    if ($player->hasPlayedCard('B33_Mantlepiece')) {
      return false;
    }

    $nbRooms = $player->countRooms();
    $args = $this->argsRenovation($player);

    foreach ($args['renovationType'] as $renovationType) {
      if ($ignoreResources || $player->canBuy($this->getCosts($player, $renovationType), $nbRooms)) {
        return true;
      }
    }
    return false;
  }

  public function isAutomatic($player = null)
  {
    return true;
  }
  
  public function argsRenovation($player = null)
  {
    if ($player == null) {
      $player = Players::getActive();
    }

    $renovationType = [$this->renovationLink[$player->getRoomType()]];

    if (
      ($player->hasPlayedCard('A87_Conservator') && $player->getRoomType() == 'roomWood') ||
      ($player->hasPlayedCard('C13_WoodSlideHammer') && $player->getRoomType() == 'roomWood' && $player->countRooms() >= 5)
    ){
      $renovationType[] = 'roomStone';
    }
    
    if ($this->getCtxArgs()['toStone'] ?? false) {
      $renovationType = ['roomStone'];
    }

    return [
      'renovationType' => $renovationType,
      'combinations' => $this->doableCombinations($player, $renovationType),
    ];
  }

  protected function doableCombinations($player, $renovationType)
  {
    $canDo = [];
    foreach ($renovationType as $type) {
      if ($player->canBuy($this->getCosts($player, $type), $player->countRooms())) {
        $canDo[] = $type;
      }
    }
    return $canDo;
  }

  public function stRenovation()
  {
    $player = Players::getActive();
    $args = $this->argsRenovation();
    $canDo = $this->doableCombinations($player, $args['renovationType']);

    if (count($canDo) == 1) {
      $this->actRenovation(array_pop($canDo));
    }
    else if (empty($canDo)) {
      throw new UserException(totranslate('Renovation is not doable'));
    }
  }

  public function actRenovation($renovationType)
  {
    self::checkAction('actRenovation');
    $player = Players::getCurrent();
    $costs = $this->getCosts($player, $renovationType); // Make sure to call it before DB update!
    $args = $this->argsRenovation();

    if (!in_array($renovationType, $args['renovationType'])) {
      throw new \feException('renovationType not authorized. Should not happen');
    }

    $roomType = $player->getRoomType();
    $newRoomType = $renovationType;
    // $newRoomType = $this->renovationLink[$roomType];

    // Renovation
    $rooms = $player->board()->renovateRooms($newRoomType);

    Notifications::renovate($player, $rooms, $newRoomType);

    // Pay and proceed
    $player->pay(count($rooms), $costs, clienttranslate('Renovation'));

    // Listeners for cards
    $eventData = [
      'oldRoomType' => $roomType,
      'newRoomType' => $newRoomType,
      'rooms' => $rooms,
    ];
    $this->checkAfterListeners($player, $eventData);

    $this->resolveAction(['rooms' => $rooms]);
    Engine::proceed();
  }
}
