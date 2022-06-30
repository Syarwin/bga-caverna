<?php
namespace AGR\Cards\D;

class D27_Retraining extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D27_Retraining';
    $this->name = ('Retraining');
    $this->deck = 'D';
    $this->number = 27;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      (
        'At the end of each turn in which you renovate, you can exchange your Joinery for the Pottery or your Pottery for the Basketmaker\'s Workshop.'
      ),
    ];
    $this->vp = 1;
    $this->cost = [
      FOOD => '1',
    ];
    $this->prerequisite = ('1 Occupation');
    $this->implemented = false;
  }
}
