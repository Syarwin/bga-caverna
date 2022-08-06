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
    $this->description = [
      'log' => clienttranslate('Place a ${tiles}'),
      'args' => [
        'i18n' => ['tiles'],
        'tiles' => $this->getTilesMsg(),
      ],
    ];
  }

  public function getState()
  {
    return ST_CONSTRUCT;
  }

  public function getTiles()
  {
    // TODO : ?? should be useless
    return $this->getCtxArgs()['tiles'] ?? [];
  }

  public function getTilesMsg()
  {
    $tileNames = [
      TILE_CAVERN_TUNNEL => clienttranslate('a Cavern/Tunnel twin tile'),
      TILE_CAVERN_CAVERN => clienttranslate('a Cavern/Cavern twin tile'),
      TILE_MEADOW_FIELD => clienttranslate('a Meadow/Field twin tile'),
      TILE_MINE_DEEP_TUNNEL => clienttranslate('a Ore Mine/Deep tunnel twin tile'),
      TILE_RUBY_MINE => clienttranslate('a Ruby mine tile'),
      TILE_MEADOW => clienttranslate('a Meadow tile'),
      TILE_FIELD => clienttranslate('a Field tile'),
    ];

    $tiles = $this->getTiles();
    if (count($tiles) == 1) {
      return $tileNames[$tiles[0]];
    } elseif (count($tiles) == 2) {
      return [
        'log' => clienttranslate('${tile1} or ${tile2}'),
        'args' => [
          'i18n' => ['tile1', 'tile2'],
          'tile1' => $tileNames[$tiles[0]],
          'tile2' => $tileNames[$tiles[1]],
        ],
      ];
    }

    return 'NOT DONE';
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
    // The player must be able place one of the tiles
    return $player->board()->canConstruct($this->getTiles());
  }

  public function argsConstruct()
  {
    $player = Players::getActive();
    $zones = [];
    foreach ($this->getTiles() as $tile) {
      $zones[$tile] = $player->board()->getPlacementOptions($tile);
    }

    return [
      'i18n' => ['tiles'],
      'tiles' => $this->getTilesMsg(),
      'zones' => $zones,
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
