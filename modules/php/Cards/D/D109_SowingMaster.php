<?php
namespace AGR\Cards\D;

class D109_SowingMaster extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D109_SowingMaster';
    $this->name = ('Sowing Master');
    $this->deck = 'D';
    $this->number = 109;
    $this->category = FOOD_PROVIDER;
    $this->desc = [
      (
        'When you play this card, you immediately get 1 <WOOD>. Each time after you use an action space with the __Sow__ action, you get 2 <FOOD>.'
      ),
    ];
    $this->players = '1+';
    $this->implemented = false;
  }

  public function onBuy($player) {
    return $this->gainNode([WOOD => 1]);
  }

  public function isListeningTo($event)
  {
    if (!$this->isActionEvent($event, 'PlaceFarmer')) {
      return false;
    }      
      
    return ($event['actionCardType'] == 'GrainUtilization') ||
      ($event['actionCardType'] == 'Cultivation');
  }

  public function onPlayerAfterPlaceFarmer($player, $event)
  {
    return $this->gainNode([FOOD => 2]);
  }
}
