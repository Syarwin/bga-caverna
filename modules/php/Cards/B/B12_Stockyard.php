<?php
namespace AGR\Cards\B;

class B12_Stockyard extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B12_Stockyard';
    $this->name = ('Stockyard');
    $this->deck = 'B';
    $this->number = 12;
    $this->category = FARM_PLANNER;
    $this->desc = [
      ('This card can hold up to 3 animals of the same type. (It is not considered a pasture).'),
    ];
    $this->vp = 1;
    $this->cost = [
      WOOD => '1',
      STONE => '1',
    ];
    $this->implemented = false;
  }
}
