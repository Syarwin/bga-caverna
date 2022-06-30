<?php
namespace AGR\Cards\B;

class B79_Corf extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B79_Corf';
    $this->name = clienttranslate('Corf');
    $this->deck = 'B';
    $this->number = 79;
    $this->category = BUILDING_RESOURCE_PROVIDER;
    $this->desc = [
      clienttranslate(
        'Each time any player (including you) takes at least 3 <STONE> from an accumulation space, you get 1 <STONE> from the general supply.'
      ),
    ];
    $this->cost = [
      REED => 1,
    ];
    $this->newSet = true;
  }
  
  public function isListeningTo($event)
  {
    return $this->isCollectEvent($event, STONE, false, null) &&
      count($event['meeples']) >= 3;
  }

  public function onPlayerAfterCollect($player, $event)
  {
    return $this->gainNode([STONE => 1]);
  }  
  
  public function onOpponentAfterCollect($player, $event)
  {
    return $this->gainNode([STONE => 1]);
  } 

}
