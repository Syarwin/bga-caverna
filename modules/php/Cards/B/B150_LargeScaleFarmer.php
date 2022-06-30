<?php
namespace AGR\Cards\B;

class B150_LargeScaleFarmer extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B150_LargeScaleFarmer';
    $this->name = ('Large-Scale Farmer');
    $this->deck = 'B';
    $this->number = 150;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      (
        'Each time after you use the "Farm Expansion" or "Major Improvement" action space while the other is unoccupied, you can pay 1 food to use that other space with the same person.'
      ),
    ];
    $this->players = '4+';
    $this->implemented = false;
  }
}
