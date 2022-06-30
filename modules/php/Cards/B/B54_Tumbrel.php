<?php
namespace AGR\Cards\B;

class B54_Tumbrel extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B54_Tumbrel';
    $this->name = ('Tumbrel');
    $this->deck = 'B';
    $this->number = 54;
    $this->category = FOOD_PROVIDER;
    $this->desc = [
      (
        'When you play this card, you immediately get 2 food. Each time after you take an unconditional "Sow" action, you get 1 food for each stable you have.'
      ),
    ];
    $this->cost = [
      WOOD => '1',
    ];
    $this->implemented = false;
  }
}
