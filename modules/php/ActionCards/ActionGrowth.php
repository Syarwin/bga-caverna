<?php
namespace CAV\ActionCards;

class ActionGrowth extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionGrowth';
    $this->name = clienttranslate('Growth');
    $this->desc = [clienttranslate("Either"), clienttranslate('or')];
    $this->tooltip = [
      clienttranslate('Take 1 Wood, 1 Stone, 1 Ore, 1 Food and 2 Gold from the general supply.'),
      clienttranslate('Alternatively, carry out a Family growth action.'),
    ];
    $this->players = [4, 5, 6, 7];
  }

  protected function getFlow($player, $dwarf)
  {
    $flow = [
      'type' => NODE_XOR,
      'childs' => [
        [
          'action' => GAIN,
          'args' => [
            WOOD => 1,
            STONE => 1,
            ORE => 1,
            FOOD => 1,
            GOLD => 2,
          ],
        ],
        [
          'action' => WISHCHILDREN,
          'args' => ['constraints' => ['freeRoom']],
        ],
      ],
    ];

    if ($player->hasPlayedBuilding('G_GuestRoom')) {
      $flow['type'] = NODE_OR;
    }

    return $flow;
  }
}
