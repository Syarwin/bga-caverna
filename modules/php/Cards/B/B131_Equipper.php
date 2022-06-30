<?php
namespace AGR\Cards\B;

class B131_Equipper extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B131_Equipper';
    $this->name = clienttranslate('Equipper');
    $this->deck = 'B';
    $this->number = 131;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      clienttranslate(
        'Immediately after each time you use a wood accumulation space, you can play a minor improvement.'
      ),
    ];
    $this->players = '3+';
    $this->newSet = true;
  }
  
  public function isListeningTo($event)
  {
    return $this->isCollectEvent($event, WOOD);
  }

  public function onPlayerAfterCollect($player, $event)
  {
    return [      
      'action' => IMPROVEMENT,
      'optional' => true,
      'args' => [
        'types' => [MINOR],
      ],
    ];
  } 
}
