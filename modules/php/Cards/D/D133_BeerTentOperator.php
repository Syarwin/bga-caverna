<?php
namespace AGR\Cards\D;

class D133_BeerTentOperator extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D133_BeerTentOperator';
    $this->name = ('Beer Tent Operator');
    $this->deck = 'D';
    $this->number = 133;
    $this->category = POINTS_PROVIDER;
    $this->desc = [
      (
        'In the feeding phase of each harvest, you can use this card to turn 1 wood plus 1 grain into 1 bonus point and 2 food.'
      ),
    ];
    $this->players = '3+';
    $this->implemented = false;
    $this->newSet = true;
  }
}
