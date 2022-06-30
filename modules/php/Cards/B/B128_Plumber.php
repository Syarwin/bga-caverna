<?php
namespace AGR\Cards\B;

class B128_Plumber extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B128_Plumber';
    $this->name = ('Plumber');
    $this->deck = 'B';
    $this->number = 128;
    $this->category = FARM_PLANNER;
    $this->desc = [
      (
        'Each time after you use the "Major Improvement" action space, you can take a "renovation" action, paying 2 clay or 2 stone less for the renovation.'
      ),
    ];
    $this->players = '3+';
    $this->implemented = false;
  }
}
