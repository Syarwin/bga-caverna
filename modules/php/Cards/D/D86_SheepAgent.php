<?php
namespace AGR\Cards\D;

class D86_SheepAgent extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D86_SheepAgent';
    $this->name = ('Sheep Agent');
    $this->deck = 'D';
    $this->number = 86;
    $this->category = FARM_PLANNER;
    $this->desc = [
      (
        'You can keep 1 sheep on each occupation card in front of you (including this one), unless it is already able to hold animals.'
      ),
    ];
    $this->players = '1+';
    $this->implemented = false;
  }
}
