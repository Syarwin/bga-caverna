<?php
namespace AGR\Cards\B;

class B18_GrasslandHarrow extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B18_GrasslandHarrow';
    $this->name = ('Grassland Harrow');
    $this->deck = 'B';
    $this->number = 18;
    $this->category = FARM_PLANNER;
    $this->desc = [
      (
        'Add 1 to the current round for each building resource in your supply and place 1 field on the corresponding round space. A the start of the round, you can plow the field.'
      ),
    ];
    $this->cost = [
      WOOD => '2',
    ];
    $this->prerequisite = ('2 Occupations, 1 Building Resource in Your Supply');
    $this->implemented = false;
    $this->newSet = true;
  }
}
