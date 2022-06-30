<?php
namespace AGR\Cards\B;
use AGR\Managers\PlayerCards;

class B49_Scales extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B49_Scales';
    $this->name = clienttranslate('Scales');
    $this->deck = 'B';
    $this->number = 49;
    $this->category = FOOD_PROVIDER;
    $this->desc = [
      clienttranslate(
        'Each time after you place an improvement or occupation in front of you, if you then have the same number of improvements and occupations in play, you get 2 <FOOD>.'
      ),
    ];
    $this->cost = [
      WOOD => 1,
    ];
    $this->prerequisite = clienttranslate('No Occupation');
	$this->occupationPrerequisites = ['max' => 0];		
    $this->newSet = true;
  }
  
  public function isListeningTo($event)
  {
    return $this->isActionEvent($event, 'Occupation') || 
	  $this->isActionEvent($event, 'Improvement');
  }

  public function onPlayerAfterOccupation($player, $event)
  {
	if ($this->getPlayer()->countOccupations() == $this->getPlayer()->countAllImprovements()) {
      return $this->gainNode([FOOD => 2]);
    }
  }

  public function onPlayerAfterImprovement($player, $event)
  {
    $card = PlayerCards::get($event['cardId']);
    if ($card->passing) {
      return;
    }
      
	if ($this->getPlayer()->countOccupations() == $this->getPlayer()->countAllImprovements()) {
      return $this->gainNode([FOOD => 2]);
    }
  }  
}
