<?php
namespace CAV\ActionCards;

class ActionSupplies extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionSupplies';
    $this->name = clienttranslate('Supplies');
    $this->tooltip = [clienttranslate('Take 1 Wood, 1 Stone, 1 Ore, 1 Food and 2 Gold from the general supply.')];
    $this->players = [1, 2, 3];

    $this->flow = [
      'action' => GAIN,
      'args' => [
        WOOD => 1,
        STONE => 1,
        ORE => 1,
        FOOD => 1,
        GOLD => 2,
      ],
    ];
  }
}
