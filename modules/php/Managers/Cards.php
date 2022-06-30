<?php
namespace AGR\Managers;

/* Class to manage all the cards for Agricola */

class Cards extends \AGR\Helpers\Pieces
{
  protected static $table = 'cards';
  protected static $prefix = 'card_';
  protected static $customFields = ['player_id'];

  protected static function cast($card)
  {
    return [
      'id' => $card['id'],
      'location' => $card['location'],
      'player_id' => $card['player_id'],
      'state' => $card['state'],
    ];
  }

  /* Creation of various the cards */
  public static function setupNewGame($players, $options)
  {
    $playerCount = count($players);

    // init major improvements
    // init occupation
    // init minor improvements (draft!)
  }
}
