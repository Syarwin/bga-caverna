<?php
namespace CAV\Managers;
use CAV\Helpers\Utils;

/* Class to manage all the tiles (cavern, tunnel, mines, meadow) for Caverna */

class Tiles extends \CAV\Helpers\Pieces
{
  protected static $table = 'tiles';
  protected static $prefix = 'tile_';
  protected static $customFields = ['player_id', 'type', 'asset', 'x', 'y'];
  protected static $autoIncrement = true;

  protected static function cast($tile)
  {
    return [
      'id' => (int) $tile['id'],
      'location' => $tile['location'],
      'pId' => $tile['player_id'],
      'type' => $tile['type'],
      'asset' => $tile['asset'],
      'x' => (int) $tile['x'],
      'y' => (int) $tile['y'],
    ];
  }

  /* Creation of the tiles */
  public static function setupNewGame($players, $options)
  {
    $tiles = [];
    foreach ($players as $pId => $player) {
      $tiles[] = [
        'type' => \TILE_CAVERN,
        'player_id' => $pId,
        'asset' => '',
        'x' => 7,
        'y' => 7,
      ];
      $tiles[] = [
        'type' => \TILE_CAVERN,
        'player_id' => $pId,
        'asset' => '',
        'x' => 7,
        'y' => 5,
      ];
    }

    // Create the tiles
    self::create($tiles, 'inPlay');
  }

  public static function getUiData()
  {
    return self::getInLocationOrdered('inPlay')->toArray();
  }

  public static function getOfPlayer($pId, $type = null)
  {
    return self::getFilteredQuery($pId, 'inPlay', $type)
      ->orderBy('tile_id')
      ->get();
  }

  public static function createTileOnBoard($tileType, $tileAsset, $pId, $x, $y)
  {
    $tile = [
      'location' => 'inPlay',
      'type' => $tileType,
      'player_id' => $pId,
      'asset' => $tileAsset,
      'x' => $x,
      'y' => $y,
    ];

    return self::singleCreate($tile);
  }

  /**
   * Generic base query
   */
  public function getFilteredQuery($pId, $location, $type)
  {
    $query = self::getSelectQuery()->wherePlayer($pId);
    if ($location != null) {
      $query = $query->where('tile_location', $location);
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
}
