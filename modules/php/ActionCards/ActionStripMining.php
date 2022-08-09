<?php
namespace CAV\ActionCards;

class ActionStripMining extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionStripMining';
    $this->name = clienttranslate('Strip Mining');
    $this->tooltip = [
      clienttranslate(
        'Take all the goods that have accumulated on this Action space. (1 Stone will be added to this Action space every round unless it is empty. Then 1 Ore will be added to it instead.)'
      ),
      clienttranslate('Additionally, you may take 2 Wood from the general supply.'),
    ];
    $this->players = [3];

    $this->accumulation = [ORE => [1, 0], STONE => [0, 1]];
    $this->flow = [
      'type' => NODE_SEQ,
      'childs' => [
        ['action' => COLLECT],
        [
          'action' => GAIN,
          'args' => [WOOD => 2],
        ],
      ],
    ];
  }
}
