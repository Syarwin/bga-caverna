<?php
namespace AGR\Cards\D;

class D60_LargePottery extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D60_LargePottery';
    $this->name = ('Large Pottery');
    $this->deck = 'D';
    $this->number = 60;
    $this->category = FOOD_PROVIDER;
    $this->desc = [('At any time: Clay → 2 Food Scoring: 3/5/6/7 Clay → 1/2/3/4 bonus points')];
    $this->vp = 3;
    $this->cost = [
      CLAY => '1',
      STONE => '1',
    ];
    $this->prerequisite = ('Return the Pottery');
    $this->implemented = false;
    $this->newSet = true;
  }
}
