<?php
namespace AGR\Cards\D;

class D61_BaleofStraw extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D61_BaleofStraw';
    $this->name = ('Bale of Straw');
    $this->deck = 'D';
    $this->number = 61;
    $this->category = FOOD_PROVIDER;
    $this->desc = [
      (
        'At the start of each harvest, if you have at least 3 grain fields (including field cards with planted grain), you get 2 food.'
      ),
    ];
    $this->implemented = false;
  }
}
