<?php
namespace AGR\Cards\B;

class B63_Tasting extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B63_Tasting';
    $this->name = ('Tasting');
    $this->deck = 'B';
    $this->number = 63;
    $this->category = FOOD_PROVIDER;
    $this->desc = [
      (
        'Each time you use a "Lessons" action space, before paying the occupation cost, you can exchange 1 grain for 4 food.'
      ),
    ];
    $this->vp = 1;
    $this->cost = [
      WOOD => '2',
    ];
    $this->implemented = false;
    $this->newSet = true;
  }
}
