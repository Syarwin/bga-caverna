<?php
namespace AGR\Cards\D;

class D53_TeaHouse extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D53_TeaHouse';
    $this->name = ('Tea House');
    $this->deck = 'D';
    $this->number = 53;
    $this->category = FOOD_PROVIDER;
    $this->desc = [
      (
        'Once per round, you can skip placing your second person and get 1 food instead. (You can place the person later that round.)'
      ),
    ];
    $this->vp = 2;
    $this->cost = [
      WOOD => '1',
      STONE => '1',
    ];
    $this->prerequisite = ('Play in Round 6 or Later');
    $this->implemented = false;
  }
}
