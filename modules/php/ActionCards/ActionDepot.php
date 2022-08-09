<?php
namespace CAV\ActionCards;

class ActionDepot extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionDepot';
    $this->name = clienttranslate('Depot');
    $this->tooltip = [
      clienttranslate(
        'Take all the goods that have accumulated on this Action space. Every round 1 Ore and 1 wood will be added to this Action space.'
      ),
    ];
    $this->players = [5, 6, 7];

    $this->accumulation = [ORE => 1, WOOD => 1];
    $this->flow = [
      'action' => COLLECT,
    ];
  }
}
