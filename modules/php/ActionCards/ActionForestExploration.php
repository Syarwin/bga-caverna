<?php
namespace CAV\ActionCards;

class ActionForestExploration extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionForestExploration';
    $this->name = clienttranslate('Forest exploration');
    $this->tooltip = [
      clienttranslate(
        'Take all the Wood that has accumulated on this Action space. (Every round, 1 Wood will be added to it.)'
      ),
      clienttranslate('Take 1 Vegetable from the general supply.'),
    ];
    $this->players = [3];

    $this->accumulation = [WOOD => 1];
    $this->flow = [
      'type' => NODE_SEQ,
      'childs' => [['action' => COLLECT], ['action' => GAIN, 'args' => [\VEGETABLE => 1]]],
    ];
  }
}
