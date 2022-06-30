<?php
namespace AGR\Cards\B;
use AGR\Core\Globals;

class B92_LittleStickKnitter extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B92_LittleStickKnitter';
    $this->name = clienttranslate('Little Stick Knitter');
    $this->deck = 'B';
    $this->number = 92;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      clienttranslate(
        'From Round 5 on, each time you use the __Sheep Market__ accumulation space, you can also take a __Family Growth with Room Only__ action.'
      ),
    ];
    $this->players = '1+';
    $this->holder = true;
    $this->newSet = true;
  }
  
  public function isListeningTo($event)
  {
    return $this->isActionCardEvent($event, 'SheepMarket');  
  }

  public function onPlayerPlaceFarmer($player, $args)
  {
    if (Globals::getTurn() >= 5) {
      return [
        'action' => WISHCHILDREN,
        'optional' => true,
        'args' => ['constraints' => ['freeRoom'], 'cardLocation' => $this->id],
        'pId' => $this->pId,
        'source' => $this->name,
      ];
    }
  }
}
