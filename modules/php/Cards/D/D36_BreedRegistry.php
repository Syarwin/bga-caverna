<?php
namespace AGR\Cards\D;

class D36_BreedRegistry extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D36_BreedRegistry';
    $this->name = ('Breed Registry');
    $this->deck = 'D';
    $this->number = 36;
    $this->category = POINTS_PROVIDER;
    $this->desc = [
      (
        'During scoring, if you gained at most 2 sheep from sources other than breeding during the game and have not turned any sheep into food, you get 3 bonus points.'
      ),
    ];
    $this->prerequisite = ('No Sheep');
    $this->implemented = false;
  }
}
