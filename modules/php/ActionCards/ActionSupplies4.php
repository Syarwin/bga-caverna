<?php
namespace CAV\ActionCards;

class ActionSupplies4 extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionSupplies4';
    $this->actionCardType = 'ActionSupplies';
    $this->name = clienttranslate('Growth');
    $this->tooltip = [
      clienttranslate('Take 1 Wood, 1 Stone, 1 Ore, 1 Food and 2 Gold from the general supply.'),
      clienttranslate('Alternatively, carry out a Family growth action.'),
    ];
    $this->players = [4, 5, 6, 7];

    $this->flow = [
      'type' => NODE_XOR,
      'childs' => [
        [
          'action' => GAIN,
          'args' => [
            WOOD => 1,
            STONE => 1,
            ORE => 1,
            FOOD => 2,
            GOLD => 2,
          ],
        ],
        [
          'action' => WISHCHILDREN,
        ],
      ],
    ];
  }
}
