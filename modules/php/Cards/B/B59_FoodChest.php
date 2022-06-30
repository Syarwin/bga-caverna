<?php
namespace AGR\Cards\B;

class B59_FoodChest extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B59_FoodChest';
    $this->name = ('Food Chest');
    $this->deck = 'B';
    $this->number = 59;
    $this->category = FOOD_PROVIDER;
    $this->desc = [
      (
        'If you play this card on the "Major Improvement" action space, you immediately get 4 food. Otherwise, you get only 2 food.'
      ),
    ];
    $this->cost = [
      WOOD => '1',
    ];
    $this->implemented = false;
  }
}
