<?php
namespace AGR\Cards\D;

class D83_Pigswill extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D83_Pigswill';
    $this->name = clienttranslate('Pigswill');
    $this->deck = 'D';
    $this->number = 83;
    $this->category = LIVESTOCK_PROVIDER;
    $this->desc = [clienttranslate('Each time you use the __Fencing__ action space, you also get 1 <PIG>.')];
    $this->costs = [[FOOD => 2], [GRAIN => 1]];
    $this->newSet = true;
  }
  
  public function isListeningTo($event)
  {
    return $this->isActionCardEvent($event, 'Fencing');
  }

  public function onPlayerPlaceFarmer($player, $event)
  {
    return $this->gainNode([PIG => 1]);
  }  
}
