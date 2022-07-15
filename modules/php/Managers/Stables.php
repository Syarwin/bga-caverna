<?php
namespace CAV\Managers;

/* Class to manage all the stables for Agricola */

class Stables extends Meeples
{
  /* Creation of various meeples */
  public static function setupNewGame($players, $options)
  {
    $meeples = [];
    foreach ($players as $pId => $player) {
      $meeples[] = ['type' => 'stable', 'player_id' => $pId, 'location' => 'reserve', 'nbr' => 3];
    }

    self::create($meeples);
  }

  /* Partial query for a given player */
  public function qPlayer($pId, $location = null)
  {
    $query = self::getSelectQuery()
      ->wherePlayer($pId)
      ->where('type', 'stable');

    if ($location != null) {
      $query = $query->where('meeple_location', $location);
    }

    return $query;
  }

  /**
   * Do we have an available stable for an action
   * @return boolean true if has one available, else false
   * @param number $pId Id of player
   */
  public function hasAvailable($pId)
  {
    return self::qPlayer($pId, 'reserve')->count() > 0;
  }

  /**
   * Provide first available stable
   * @param number $pId
   * @return array meeple
   */
  public function getNextAvailable($pId)
  {
    return self::qPlayer($pId, 'reserve')->getSingle();
  }

  /**
   * move stable token. Take the next available stable
   * @param number $pId
   * @param varchar $location place on which we put the card
   * @param array $coord X & Y position
   * CAUTION : don't use 'move' as it's already taken by parent
   **/
  public function moveNextAvailable($pId, $location, $coords = null)
  {
    $stable = self::getNextAvailable($pId);
    if ($stable == null) {
      throw new \feException('No more available stable');
    }

    parent::moveToCoords($stable['id'], $location, $coords);
    return $stable['id'];
  }

  /**
   * @param number $pId
   * @return int number of farmers
   **/
  public function count($pId)
  {
    return self::qPlayer($pId)
      ->where('meeple_location', '<>', 'reserve')
      ->count();
  }

  public function countAvailable($pId)
  {
    return self::qPlayer($pId)
      ->where('meeple_location', 'reserve')
      ->count();
  }

  /**
   * Provide placed stables
   * @param number $pId
   * @return array meeples
   */
  public function getOnBoard($pId)
  {
    return self::qPlayer($pId, 'board')->get();
  }
}
