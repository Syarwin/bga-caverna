<?php
namespace AGR\Cards\B;

class B85_FarmHand extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B85_FarmHand';
    $this->name = ('Farm Hand');
    $this->deck = 'B';
    $this->number = 85;
    $this->category = FARM_PLANNER;
    $this->desc = [
      (
        'Once you have 4 field tiles arranged in a 2x2, you can use a "Build Stables" action to build a stable in the center of the 2x2. This stable provides room for a person but no animal.'
      ),
    ];
    $this->players = '1+';
    $this->implemented = false;
  }
}
