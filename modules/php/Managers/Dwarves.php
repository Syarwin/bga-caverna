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

  /* Add the weapons to a collection of dwarves */
  protected function addWeapons(&$dwarves, $pId = null)
  {
    $weapons = Meeples::getFilteredQuery($pId, null, 'weapon')->get();
    foreach ($weapons as $w) {
      if (isset($dwarves[$w['location']])) {
        $dwarves[$w['location']]['weapon'] = $w['state'];
        $dwarves[$w['location']]['weaponId'] = $w['id'];
      }
    }
  }

  /* Automatically Add the weapons when fetching dwarves */

  public static function getMany($ids, $raiseExceptionIfNotEnough = true)
  {
    $dwarves = parent::getMany($ids, $raiseExceptionIfNotEnough);
    self::addWeapons($dwarves);
    return $dwarves;
  }

  /* Partial query for a given player */
  public function qPlayer($pId, $location = null)
  {
    return self::getFilteredQuery($pId, $location, 'dwarf');
  }

  /**
   * Get all the dwarves not in reserve
   * @return Collection of dwarves
   * @param number $pId Id of player
   */
  public function getAllOfPlayer($pId)
  {
    $dwarves = self::qPlayer($pId, null)
      ->where('meeple_location', '<>', 'reserve')
      ->get();
    self::addWeapons($dwarves, $pId);
    return $dwarves;
  }

  /**
   * Do we have an available dwarf for an action?
   * @return boolean true if has one available, else false
   * @param number $pId Id of player
   */
  public function hasAvailable($pId)
  {
    return self::qPlayer($pId, 'board')->count() > 0;
  }

  public function getAllAvailable($pId = null)
  {
    $dwarves = self::qPlayer($pId, 'board')->get();
    self::addWeapons($dwarves, $pId);
    return $dwarves;
  }

  /**
   * @param number $pId
   * @return int number of dwarves
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
    return self::qPlayer($pId, 'reserve')->count() > 0;
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
   * Return all dwarves on a card
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
   * Add a weapon to a dwarf
   */
  public static function equipWeapon($dwarf, $force)
  {
    return self::singleCreate([
      'type' => 'weapon',
      'player_id' => $dwarf['pId'],
      'location' => $dwarf['id'],
      'state' => $force,
      'nbr' => 1,
    ]);
  }

  public static function upgradeWeapon($dwarf, $inc)
  {
    return self::DB()->update(['meeple_state' => ($dwarf['weapon'] ?? 0) + $inc], $dwarf['weaponId']);
  }
}
