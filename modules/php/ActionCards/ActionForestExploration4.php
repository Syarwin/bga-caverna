<?php
namespace CAV\ActionCards;

class ActionForestExploration4 extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionForestExploration4';
    $this->actionCardType = 'ActionForestExploration';
    $this->name = clienttranslate('Forest exploration');
    $this->tooltip = [
      clienttranslate(
        'Take all the Wood that has accumulated on this Action space. (Every round, 1 Wood will be added to it unless it is empty. Then 2 Wood will be added to it instead.)'
      ),
      clienttranslate('Take 2 Food from the general supply.'),
    ];
    $this->players = [4, 5, 6, 7];

    $this->accumulation = [WOOD => [2, 1]];
    $this->flow = [
      'type' => NODE_SEQ,
      'childs' => [['action' => COLLECT], ['action' => GAIN, 'args' => [FOOD => 2]]],
    ];
  }
}
