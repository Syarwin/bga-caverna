<?php
namespace AGR\Cards\B;

class B21_HayloftBarn extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B21_HayloftBarn';
    $this->name = ('Hayloft Barn');
    $this->deck = 'B';
    $this->number = 21;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      (
        'Place 4 food on this card. Each time you obtain at least 1 grain, you also get 1 food from this card. Once it is empty, you get a "Family Growth Even without Room" action.'
      ),
    ];
    $this->cost = [
      WOOD => '3',
    ];
    $this->prerequisite = ('1 Occupation');
    $this->implemented = false;
  }
}
