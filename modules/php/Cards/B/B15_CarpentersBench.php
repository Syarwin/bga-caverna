<?php
namespace AGR\Cards\B;

class B15_CarpentersBench extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B15_CarpentersBench';
    $this->name = ("Carpenter's Bench");
    $this->deck = 'B';
    $this->number = 15;
    $this->category = FARM_PLANNER;
    $this->desc = [
      (
        'Immediately after each time you use a wood accumulation space, you can use the taken wood (and only that) to build exactly 1 pasture. If you do, one of the fences is free.'
      ),
    ];
    $this->cost = [
      WOOD => '1',
    ];
    $this->implemented = false;
  }
}
