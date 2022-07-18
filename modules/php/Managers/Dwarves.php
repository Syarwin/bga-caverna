<?php
namespace CAV\Managers;

use CAV\Core\Globals;

/* Class to manage all the famers for Agricola */

class Dwarves extends Meeples
{
  /* Creation of various meeples */
  public static function setupNewGame($players, $options)
  {
    $meeples = [];
    foreach ($players as $pId => $player) {
      // Dwarves in reserve
      $meeples[] = ['type' => 'dwarf', 'player_id' => $pId, 'location' => 'reserve', 'nbr' => 3];
      // Dwarves in position
      $meeples[] = [
        'type' => 'dwarf',
        'player_id' => $pId,
        'location' => 'board',
        'x' => 7,
        'y' => 7,
        'nbr' => 2,
      ];
    }

    self::create($meeples);
  }

  /* Partial query for a given player */
  public function qPlayer($pId, $location = null)
  {
    $query = self::getSelectQuery()
      ->wherePlayer($pId)
      ->where('type', 'dwarf');

    if ($location != null) {
      $query = $query->where('meeple_location', $location);
    }

    return $query;
  }

  /**
   * Get all the farmers not in reserve
   * @return Collection of farmers
   * @param number $pId Id of player
   */
  public function getAllOfPlayer($pId)
  {
    return self::qPlayer($pId, null)
      ->where('meeple_location', '<>', 'reserve')
      ->get();
  }

  /**
   * Do we have an available farmer for an action?
   * @return boolean true if has one available, else false
   * @param number $pId Id of player
   */
  public function hasAvailable($pId)
  {
    return self::qPlayer($pId, 'board')->count() + self::qPlayer($pId, 'B10_Caravan')->count() > 0;
  }

  public function getAllAvailable($pId = null)
  {
    return self::qPlayer($pId, 'board')
      ->get()
      ->merge(self::qPlayer($pId, 'B10_Caravan')->get());
  }

  public function getNextChildren($pId)
  {
    return self::qPlayer($pId, null)
      ->where('meeple_location', '<>', 'reserve')
      ->where('meeple_state', CHILD)
      ->get();
  }

  public function hasChildren($pId)
  {
    return self::qPlayer($pId, null)
      ->where('meeple_location', '<>', 'reserve')
      ->where('meeple_state', CHILD)
      ->count() > 0;
  }

  /**
   * Provide first available farmer
   * @param number $pId
   * @return array meeple
   */
  public function getNextAvailable($pId)
  {
    // return self::qPlayer($pId, 'board')->getSingle();
    return self::getAllAvailable($pId)->first();
  }

  /**
   * move Farmer token. Take the next available farmer
   * @param number $pId
   * @param varchar $location place on which we put the card
   * @param array $coord X & Y position
   * CAUTION : don't use 'move' as it's already taken by parent
   **/
  public function moveNextAvailable($pId, $location, $coords = null)
  {
    // if (Globals::getAdoptiveChild() != 0) {
    //   $farmer = self::get(Globals::getAdoptiveChild());
    //   Globals::setAdoptiveChild(0);
    // } else {
    $farmer = self::getNextAvailable($pId);
    // }

    if ($farmer == null) {
      throw new \feException('No more available person');
    }

    parent::moveToCoords($farmer['id'], $location, $coords);
    return $farmer['id'];
  }

  /**
   * @param number $pId
   * @return int number of farmers
   **/
  public function count($pId, $type = null)
  {
    $query = self::qPlayer($pId)->where('meeple_location', '<>', 'reserve');

    if ($type !== null) {
      $query = $query->where('meeple_state', $type);
    }

    return $query->count();
  }

  /**
   *
   * @param number $pId
   * @return boolean true if it's possible
   */
  public function hasInReserve($pId)
  {
    return self::qPlayer($pId)
      ->where('meeple_location', 'reserve')
      ->count() > 0;
  }

  /**
   *
   * @param number $pId
   * @return a meeple
   */
  public function getNextInReserve($pId)
  {
    return self::qPlayer($pId, 'reserve')->getSingle();
  }

  /**
   * Return all farmers on a card
   * @param number $cId card Id
   * @param number $pId (opt)
   */
  public static function getOnCard($cId, $pId = null)
  {
    return parent::getOnCardQ($cId, $pId)
      ->where('type', 'dwarf')
      ->get();
  }

  /**
   * Get all children and make them grown-up
   */
  public static function growChildren()
  {
    $children = self::qPlayer(null)
      ->where('meeple_state', CHILD)
      ->get()
      ->getIds();
    foreach ($children as $cId) {
      self::setState($cId, ADULT);
    }
    return $children;
  }

  /**
   * Grow one child
   */
  public static function growOneChild($pId)
  {
    $children = self::qPlayer($pId)
      ->where('meeple_state', CHILD)
      ->limit(1)
      ->get()
      ->getIds();
    foreach ($children as $cId) {
      self::setState($cId, ADULT);
    }

    return $children;
  }
}
