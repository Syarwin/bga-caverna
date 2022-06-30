<?php
namespace AGR\Cards\D;
use AGR\Managers\Players;

// TODO: you can stack crazy amounts of food on 1 space, css isn't really set up for this

class D106_WhiskyDistiller extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D106_WhiskyDistiller';
    $this->name = clienttranslate('Whisky Distiller');
    $this->deck = 'D';
    $this->number = 106;
    $this->category = FOOD_PROVIDER;
    $this->desc = [
      clienttranslate(
        'At any time, you can pay 1 <GRAIN>. If you do, add 2 to the current round and place 4 <FOOD> on the corresponding round space. At the start of that round, you get the <FOOD>.'
      ),
    ];
    $this->players = '1+';
    $this->newSet = true;
  }
  
  public function isListeningTo($event)
  {
    return $this->isAnytime($event);
  }
  
  public function onPlayerAtAnytime($player, $event)
  {
    return [
      'type' => NODE_SEQ,
      'childs' => [
	    $this->payNode([GRAIN => 1]),
        $this->futureMeeplesNode([FOOD => 4], ['+2']),
      ]
    ];
  }  
}
