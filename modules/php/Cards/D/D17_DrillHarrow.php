<?php
namespace AGR\Cards\D;

class D17_DrillHarrow extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D17_DrillHarrow';
    $this->name = ('Drill Harrow');
    $this->deck = 'D';
    $this->number = 17;
    $this->category = FARM_PLANNER;
    $this->desc = [
      ('Each time before you take an unconditional "Sow" action, you can pay 3 food to plow 1 field.'),
    ];
    $this->cost = [
      WOOD => '1',
    ];
    $this->implemented = false;
    $this->newSet = true;
  }
}
