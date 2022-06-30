<?php
namespace AGR\Cards\D;

class D33_SummerHouse extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D33_SummerHouse';
    $this->name = ('Summer House');
    $this->deck = 'D';
    $this->number = 33;
    $this->category = POINTS_PROVIDER;
    $this->desc = [
      (
        'During scoring, if you live in a stone house, you get 2 bonus points for each unused farmyard space orthogonally adjacent to your house. (You still lose the points for these unused spaces.)'
      ),
    ];
    $this->cost = [
      WOOD => '3',
      STONE => '1',
    ];
    $this->prerequisite = ('Still in Wooden House');
    $this->implemented = false;
  }
}
