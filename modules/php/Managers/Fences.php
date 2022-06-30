<?php
namespace AGR\Managers;

/* Class to manage all the famers for Agricola */

class Fences extends Meeples
{
  /* Creation of various meeples */
  public static function setupNewGame($players, $options)
  {
  }

  /* Partial query for a given player */
  public function qPlayer($pId, $location = null)
  {
    $query = self::getSelectQuery()
      ->wherePlayer($pId)
      ->where('type', 'fence');

    if ($location != null) {
      $query = $query->where('meeple_location', $location);
    }

    return $query;
  }

  /**
   * Do we have an available fence for an action
   * @return boolean true if has one available, else false
   * @param number $pId Id of player
   */
  public function hasAvailable($pId)
  {
    return self::qPlayer($pId, 'reserve')->count() > 0;
  }

  /**
   * Provide first available fence
   * @param number $pId
   * @return array meeple
   */
  public function getNextAvailable($pId)
  {
    return self::qPlayer($pId, 'reserve')->getSingle();
  }

  /**
   * move Fence token. Take the next available fence
   * @param number $pId
   * @param varchar $location place on which we put the card
   * @param array $coord X & Y position
   * CAUTION : don't use 'move' as it's already taken by parent
   **/
  public function moveNextAvailable($pId, $location, $coords = null)
  {
    $fence = self::getNextAvailable($pId);
    if ($fence == null) {
      throw new \feException('No more available fence');
    }

    parent::moveToCoords($fence['id'], $location, $coords);
    return $fence['id'];
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

  public static function calculateMinimumPastureFences($pid)
  {
    //TODO: Timothée, parcours de graphe? :)
    return 4;
  }

  /**
   * Provide placed fences
   * @param number $pId
   * @return array meeples
   */
  public function getOnBoard($pId)
  {
    return self::qPlayer($pId, 'board')->get();
  }
}
