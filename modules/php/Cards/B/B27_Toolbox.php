<?php
namespace AGR\Cards\B;

class B27_Toolbox extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B27_Toolbox';
    $this->name = ('Toolbox');
    $this->deck = 'B';
    $this->number = 27;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      (
        'In the work phase, after each turn in which you build at least 1 room, stable, or fence, you can build the "Joinery", "pottery", or Basketmaker\'s Workshop" major improvement.'
      ),
    ];
    $this->cost = [
      WOOD => '1',
    ];
    $this->implemented = false;
  }
}
