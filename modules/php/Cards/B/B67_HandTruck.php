<?php
namespace AGR\Cards\B;

class B67_HandTruck extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B67_HandTruck';
    $this->name = ('Hand Truck');
    $this->deck = 'B';
    $this->number = 67;
    $this->category = CROP_PROVIDER;
    $this->desc = [
      (
        'Each time before you take a "Bake Bread" action, you also get 1 grain for each of your people occupying an accumulation space.'
      ),
    ];
    $this->cost = [
      WOOD => '1',
    ];
    $this->implemented = false;
    $this->newSet = true;
  }
}
