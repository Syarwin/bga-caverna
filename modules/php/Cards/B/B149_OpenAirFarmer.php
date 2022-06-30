<?php
namespace AGR\Cards\B;

class B149_OpenAirFarmer extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B149_OpenAirFarmer';
    $this->name = ('Open Air Farmer');
    $this->deck = 'B';
    $this->number = 149;
    $this->category = FARM_PLANNER;
    $this->desc = [
      (
        'When you play this card, you remove exactly 3 stables in your supply from play to build a pasture covering 2 farmyard spaces. You only need to pay a total of 2 wood for fences'
      ),
    ];
    $this->players = '4+';
    $this->implemented = false;
  }
}
