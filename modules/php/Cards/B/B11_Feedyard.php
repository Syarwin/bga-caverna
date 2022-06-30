<?php
namespace AGR\Cards\B;

class B11_Feedyard extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B11_Feedyard';
    $this->name = ('Feedyard');
    $this->deck = 'B';
    $this->number = 11;
    $this->category = FARM_PLANNER;
    $this->desc = [
      (
        'This card can hold 1 animal for each pasture you have, even different types. After the breeding phase of each harvest, you get 1 food for each unused spot on this card.'
      ),
    ];
    $this->vp = 1;
    $this->cost = [
      CLAY => '1',
      GRAIN => '1',
    ];
    $this->implemented = false;
  }
}
