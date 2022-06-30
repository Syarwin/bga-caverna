<?php
namespace AGR\Managers;

/* Class to manage all the major improvements of Agricola */

class MajorCards extends PlayerCards
{
  /*
   * Add base filter to keep only the major cards
   */
  protected static function addBaseFilter(&$query)
  {
    $query = $query->where('card_id', 'LIKE', 'Major_%');
  }

  public static function getUiData()
  {
    return self::getAvailables()->ui();
  }

  public static function getAvailables($type = null)
  {
    return self::getInLocation('board');
  }
}
