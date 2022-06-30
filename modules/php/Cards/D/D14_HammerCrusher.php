<?php
namespace AGR\Cards\D;

class D14_HammerCrusher extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D14_HammerCrusher';
    $this->name = ('Hammer Crusher');
    $this->deck = 'D';
    $this->number = 14;
    $this->category = FARM_PLANNER;
    $this->desc = [
      (
        'Immediately before you renovate to stone, you get 2 clay and 1 reed and you can take a "Build Rooms" action.'
      ),
    ];
    $this->cost = [
      WOOD => '1',
    ];
    $this->implemented = false;
  }
}
