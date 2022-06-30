<?php
namespace AGR\Cards\B;

class B22_WalkingBoots extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B22_WalkingBoots';
    $this->name = ('Walking Boots');
    $this->deck = 'B';
    $this->number = 22;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      (
        'You immediately get 2 food. You must immediately place a person from your supply. If you do, in the next returning home phase, you must remove that person from play.'
      ),
    ];
    $this->prerequisite = ('At Most 4 People');
    $this->implemented = false;
  }
}
