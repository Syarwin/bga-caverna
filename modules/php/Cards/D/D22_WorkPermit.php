<?php
namespace AGR\Cards\D;

class D22_WorkPermit extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D22_WorkPermit';
    $this->name = ('Work Permit');
    $this->deck = 'D';
    $this->number = 22;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      (
        'Add 1 to the current round for each building resource you have and place 1 person from your supply on the corresponding round space. In that round, you can use the person.'
      ),
    ];
    $this->cost = [
      FOOD => '1',
    ];
    $this->prerequisite = ('At Least 1 Building Resource in Your Supply');
    $this->implemented = false;
  }
}
