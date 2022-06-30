<?php
namespace AGR\Cards\D;

class D56_FatstockStretcher extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D56_FatstockStretcher';
    $this->name = ('Fatstock Stretcher');
    $this->deck = 'D';
    $this->number = 56;
    $this->category = FOOD_PROVIDER;
    $this->desc = [
      (
        'Each time you turn a sheep or wild boar into food using a baking improvement, you get 1 additional food.'
      ),
    ];
    $this->cost = [
      WOOD => '1',
    ];
    $this->implemented = false;
  }
}
