<?php

namespace CAV\Managers;

use CAV\Core\Globals;

/* Class to manage all the famers for Agricola */

class Dwarfs extends Meeples
{
  /* Creation of various meeples */
  public static function setupNewGame($players, $options)
  {
    $meeples = [];
    foreach ($players as $pId => $player) {
      // Dwarfs in reserve
      $meeples[] = ['type' => 'dwarf', 'player_id' => $pId, 'location' => 'reserve', 'nbr' => 3];
      // Dwarfs in position
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

  /* Add the weapons to a collection of dwarfs */
  protected static function addWeapons(&$dwarfs, $pId = null)
  {
    $weapons = Meeples::getFilteredQuery($pId, null, 'weapon')->get();
    foreach ($weapons as $w) {
      if (isset($dwarfs[$w['location']])) {
        $dwarfs[$w['location']]['weapon'] = $w['state'];
        $dwarfs[$w['location']]['weaponId'] = $w['id'];
      }
    }
  }

  /* Automatically Add the weapons when fetching dwarfs */

  public static function getMany($ids, $raiseExceptionIfNotEnough = true)
  {
    $dwarfs = parent::getMany($ids, $raiseExceptionIfNotEnough);
    self::addWeapons($dwarfs);
    return $dwarfs;
  }

  /* Partial query for a given player */
  public static function qPlayer($pId, $location = null)
  {
    return self::getFilteredQuery($pId, $location, 'dwarf');
  }

  /**
   * Get all the dwarfs not in reserve
   * @return Collection of dwarfs
   * @param number $pId Id of player
   */
  public static function getAllOfPlayer($pId)
  {
    $dwarfs = self::qPlayer($pId, null)
      ->where('meeple_location', '<>', 'reserve')
      ->get();
    self::addWeapons($dwarfs, $pId);
    return $dwarfs;
  }

  /**
   * Do we have an available dwarf for an action?
   * @return boolean true if has one available, else false
   * @param number $pId Id of player
   */
  public static function hasAvailable($pId)
  {
    return self::qPlayer($pId, 'board')->count() > 0;
  }

  public static function getAllAvailable($pId = null)
  {
    $dwarfs = self::qPlayer($pId, 'board')->get();
    self::addWeapons($dwarfs, $pId);
    return $dwarfs;
  }

  /**
   * @param number $pId
   * @return int number of dwarfs
   **/
  public static function count($pId, $type = null)
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
  public static function hasInReserve($pId)
  {
    return self::qPlayer($pId, 'reserve')->count() > 0;
  }

  /**
   *
   * @param number $pId
   * @return a meeple
   */
  public static function getNextInReserve($pId)
  {
    return self::qPlayer($pId, 'reserve')->getSingle();
  }

  /**
   * Return all dwarfs on a card
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
