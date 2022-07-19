<?php
namespace CAV\ActionCards;

class ActionWoodGathering extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionWoodGathering';
    $this->name = clienttranslate('Wood gathering');
    $this->tooltip = [
      clienttranslate(
        'Take all the Wood that has accumulated on this Action space. (Every round, 1 Wood will be added to it.)'
      ),
    ];
    $this->players = [1, 2, 3];

    $this->accumulation = [WOOD => 1];
    $this->flow = [
      'action' => COLLECT,
    ];
  }
}
