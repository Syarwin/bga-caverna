<?php
namespace AGR\Cards\B;

class B42_ForestInn extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B42_ForestInn';
    $this->name = ('Forest Inn');
    $this->deck = 'B';
    $this->number = 42;
    $this->category = GOODS_PROVIDER;
    $this->desc = [
      (
        'This is an action space for all. A player who uses it can exchange 5/7/9 wood for 8 wood and 2/4/7 food. When another player uses it, they must first pay you 1 food.'
      ),
    ];
    $this->cost = [
      CLAY => '1',
      REED => '1',
    ];
    $this->prerequisite = ('Play in Round 6 or Before');
    $this->implemented = false;
  }
}
