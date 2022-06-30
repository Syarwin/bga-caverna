<?php
namespace AGR\Core;
use caverna;

/*
 * Game: a wrapper over table object to allow more generic modules
 */
class Game
{
  public static function get()
  {
    return caverna::get();
  }
}
