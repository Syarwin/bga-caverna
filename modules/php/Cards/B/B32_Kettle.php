<?php
namespace AGR\Cards\B;

class B32_Kettle extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B32_Kettle';
    $this->name = ('Kettle');
    $this->deck = 'B';
    $this->number = 32;
    $this->category = POINTS_PROVIDER;
    $this->desc = [('At any time, you can exchange 1/3/5 grain for 3/4/5 food and 0/1/2 bonus points.')];
    $this->cost = [
      CLAY => '1',
    ];
    $this->prerequisite = ('1 Grain Field');
    $this->implemented = false;
    $this->newSet = true;
  }
}
