<?php
namespace CAV\Actions;

use CAV\Managers\Meeples;
use CAV\Managers\Players;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Helpers\Utils;
use CAV\Core\Stats;

class Construct extends \CAV\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = clienttranslate('Build tiles');
  }

  public function getState()
  {
    return ST_CONSTRUCT;
  }

  public function getCosts($player)
  {
    return [];
    // TODO: to see later on
    $roomType = $player->getRoomType();
    $constructCost = [];
    $costs = $constructCost[$roomType] ?? [];
    $costs = $this->getCtxArgs()['costs'] ?? $costs;
    $this->checkCostModifiers($costs, $player, ['type' => $roomType]);
    return $costs;
  }

  public function isDoable($player, $ignoreResources = false)
  {
    // The player must be able to buy at least one room and have an empty spot
    return ($ignoreResources || $player->canBuy($this->getCosts($player))) &&
      $player->board()->canConstruct($this->getCtxArgs()['tiles'] ?? []);
  }

  public function getMaxBuildableRooms($player)
  {
    $argMax = $this->ctx->getArgs()['max'] ?? 99; // Are there any upper bound linked to the action itself (in case the action is triggered by a player card)
    $maxBuyable = $player->maxBuyableAmount($this->getCosts($player));
    return min($argMax, $maxBuyable);
  }

  public function argsConstruct()
  {
    $player = Players::getActive();
    $roomType = $player->getRoomType();

    return [
      'roomType' => $roomType,
      'max' => self::getMaxBuildableRooms($player),
      'zones' => $player->board()->getFreeZones(),
    ];
  }

  public function actConstruct($rooms)
  {
    self::checkAction('actConstruct');

    $player = Players::getCurrent();
    $roomType = $player->getRoomType();
    $args = $this->getCtxArgs();
    $source = $args['source'] ?? null;

    if (count($rooms) > $this->getMaxBuildableRooms($player)) {
      throw new \BgaVisibleSystemException('You can\'t build that many rooms with your resources');
    }

    // Record amount of rooms prior to building (A21)
    $oldRoomCount = $player->countRooms();

    // Add them to board
    $playerBoard = $player->board();
    foreach ($rooms as &$room) {
      $playerBoard->addRoom($roomType, $room);
    }

    // Then run sanity checks with $raiseException = true to auto rollback in case of invalid choice
    $playerBoard->areRoomsValid(true);

    // If everything is fine, notify the new rooms
    Notifications::construct($player, $rooms, $source);

    // Insert PAY action and proceed
    $player->pay(count($rooms), $this->getCosts($player), clienttranslate('Construction'));
    Stats::incTotalRoomsBuilt($player, count($rooms));

    // Listeners for cards
    $eventData = [
      'roomType' => $roomType,
      'rooms' => $rooms,
      'oldRoomCount' => $oldRoomCount,
    ];
    $this->checkAfterListeners($player, $eventData);

    $this->resolveAction();
  }
}
