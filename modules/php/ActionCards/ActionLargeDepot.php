<?php
namespace CAV\ActionCards;

class ActionLargeDepot extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionLargeDepot';
    $this->name = clienttranslate('Large Depot');
    $this->tooltip = [
      clienttranslate(
        'Take all the goods that have accumulated on this Action space. Every round 1 Stone, 1 Ore and 1 wood will be added to this Action space. If empty, 1 additionnal wood will be placed'
      ),
    ];
    $this->players = [7];

    $this->accumulation = [ORE => 1, WOOD => [2, 1], STONE => 1];
    $this->flow = [
      'action' => COLLECT,
    ];
  }
}
