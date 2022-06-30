<?php
namespace AGR\Cards\D;

class D112_YoungFarmer extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D112_YoungFarmer';
    $this->name = clienttranslate('Young Farmer');
    $this->deck = 'D';
    $this->number = 112;
    $this->category = CROP_PROVIDER;
    $this->desc = [
      clienttranslate(
        'Each time you use the __Major Improvement__ action space, you also get 1 <GRAIN> and, afterward, you can take a __Sow__ action.'
      ),
    ];
    $this->players = '1+';
    $this->newSet = true;
  }
  
  public function isListeningTo($event)
  {
    return ($this->isActionCardEvent($event, 'MajorImprovement')) || 
      ($this->isActionEvent($event, 'PlaceFarmer') && $event['actionCardType'] == 'MajorImprovement');
  }
  
  public function onPlayerPlaceFarmer($player, $event)
  {
    return $this->gainNode([GRAIN => 1]);
  }  
  
  public function onPlayerAfterPlaceFarmer($player, $event)
  {
    return [
      'action' => SOW,
      'optional' => true,
    ];
  }  
}
