<?php
namespace AGR\Cards\B;

class B139_ForestScientist extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B139_ForestScientist';
    $this->name = ('Forest Scientist');
    $this->deck = 'B';
    $this->number = 139;
    $this->category = FOOD_PROVIDER;
    $this->desc = [
      (
        'In the returning home phase of each round, if there is no wood left on the game board, you get 1 food—from round 5 on, even 2 food.'
      ),
    ];
    $this->players = '3+';
    $this->implemented = false;
    $this->newSet = true;
  }
}
