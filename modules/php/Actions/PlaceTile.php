<?php
namespace CAV\Actions;

use CAV\Managers\Players;
use CAV\Managers\Buildings;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Helpers\Utils;
use CAV\Core\Stats;

class PlaceTile extends \CAV\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = [
      'log' => clienttranslate('Place ${tiles}'),
      'args' => [
        'i18n' => ['tiles'],
        'tiles' => $this->getTilesMsg(),
      ],
    ];
  }

  public function getState()
  {
    return ST_PLACE_TILE;
  }

  public function getTiles()
  {
    // TODO : ?? should be useless
    return $this->getCtxArgs()['tiles'] ?? [];
  }

  public static function getTileName($tile)
  {
    $tileNames = [
      TILE_TUNNEL_CAVERN => clienttranslate('a Cavern/Tunnel twin tile'),
      TILE_CAVERN_CAVERN => clienttranslate('a Cavern/Cavern twin tile'),
      TILE_MEADOW_FIELD => clienttranslate('a Meadow/Field twin tile'),
      TILE_MINE_DEEP_TUNNEL => clienttranslate('a Ore Mine/Deep tunnel twin tile'),
      TILE_RUBY_MINE => clienttranslate('a Ruby mine tile'),
      TILE_MEADOW => clienttranslate('a Meadow tile'),
      TILE_FIELD => clienttranslate('a Field tile'),
      TILE_PASTURE => clienttranslate('a Small Pasture'),
      TILE_LARGE_PASTURE => clienttranslate('a Large Pasture'),
    ];
    return $tileNames[$tile];
  }

  public function getTilesMsg()
  {
    $tiles = $this->getTiles();
    if (count($tiles) == 1) {
      return self::getTileName($tiles[0]);
    } elseif (count($tiles) == 2) {
      return [
        'log' => clienttranslate('${tile1} or ${tile2}'),
        'args' => [
          'i18n' => ['tile1', 'tile2'],
          'tile1' => self::getTileName($tiles[0]),
          'tile2' => self::getTileName($tiles[1]),
        ],
      ];
    }

    return 'NOT DONE';
  }

  public function isDoable($player, $ignoreResources = false)
  {
    // The player must be able place one of the tiles
    // TODO : handle the cost
    return $player->board()->canPlace($this->getTiles());
  }

  public function argsPlaceTile()
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

  public function getCosts($player, $args = [])
  {
    $costs = [];

    if (isset($args['costs']) && $args['costs'] != null) {
      $costs['trades'][] = $args['costs'];
    }

    // Apply card effects
    $args['card'] = $this;
    $args['costs'] = $costs;
    Buildings::applyEffects($player, 'ComputePlaceTileCosts', $args);
    return $args['costs'];
  }

  public function actPlaceTile($tile, $positions)
  {
    self::checkAction('actPlaceTile');

    $player = Players::getCurrent();
    $args = $this->argsPlaceTile();
    $zones = $this->argsPlaceTile()['zones'][$tile] ?? [];
    Utils::filter($zones, function ($zone) use ($positions) {
      if (count($positions) == 2) {
        return $positions[0] == $zone['pos1'] && $positions[1] == $zone['pos2'];
      } else {
        return $positions[0] == $zone;
      }
    });
    if (empty($zones)) {
      throw new \BgaVisibleSystemException('You can\'t place that tile here');
    }

    // Add them to board
    $playerBoard = $player->board();
    list($squares, $bonus) = $playerBoard->addTile($tile, $positions);

    // Notify the new tile squares
    Notifications::placeTile($player, $tile, $squares);

    // Trigger of Pay if needed
    $cost = $this->getCosts($player, $this->getCtxArgs());
    if ($cost != NO_COST) {
      $player->pay(1, $cost, clienttranslate('place tile'));
    }

    // Insert gain node if any bonus
    if (!is_null($bonus)) {
      Engine::insertAsChild([
        'action' => GAIN,
        'pId' => $player->getId(),
        'args' => $bonus,
      ]);
    }

    // TODO : stats
    //    Stats::incTotalRoomsBuilt($player, count($rooms));

    // Listeners for cards
    $eventData = [
      'tile' => $tile,
      'positions' => $positions,
      'bonus' => $bonus,
    ];
    $this->checkAfterListeners($player, $eventData);

    $this->resolveAction();
  }
}
