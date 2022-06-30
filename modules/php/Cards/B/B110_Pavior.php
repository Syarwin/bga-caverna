<?php
namespace AGR\Cards\B;
use AGR\Core\Globals;

class B110_Pavior extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B110_Pavior';
    $this->name = clienttranslate('Pavior');
    $this->deck = 'B';
    $this->number = 110;
    $this->category = FOOD_PROVIDER;
    $this->desc = [
      clienttranslate(
        'At the end of each preparation phase, if you have at least 1 <STONE> in your supply, you get 1 <FOOD>. In round 14, you get 1 <VEGETABLE> instead.'
      ),
    ];
    $this->players = '1+';
    $this->newSet = true;
  }
  
  public function isListeningTo($event)
  {
    return $this->isPlayerEvent($event) && $event['type'] == 'BeforeStartOfTurn';
  }
  
  public function onPlayerBeforeStartOfTurn($player, $event)
  {
    $stone = $player->countReserveResource(STONE);
    
    if ($stone > 0) {
      // since we didn't distinguish the timing from "before the start of the turn", the turn counter is not updated yet
      $resource = Globals::getTurn() == 13 ? VEGETABLE : FOOD; 
      return $this->gainNode([$resource => 1]);
    }
  }
}
