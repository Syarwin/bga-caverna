<?php
namespace AGR\Cards\D;

class D63_Lynchet extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D63_Lynchet';
    $this->name = ('Lynchet');
    $this->deck = 'D';
    $this->number = 63;
    $this->category = FOOD_PROVIDER;
    $this->desc = [
      (
        'In the field phase of each harvest, you get 1 food for each harvested field tile that is orthogonally adjacent to your house.'
      ),
    ];
    $this->implemented = false;
  }
}
