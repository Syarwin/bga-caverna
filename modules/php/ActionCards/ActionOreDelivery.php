<?php
namespace CAV\ActionCards;

class ActionOreDelivery extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionOreDelivery';
    $this->name = clienttranslate('Ore Delivery');
    $this->desc = [clienttranslate('for each')];
    $this->tooltip = [
      clienttranslate(
        'Take all the Ore that has accumulated on this Action space. (Every round, 1 Ore and 1 Stone will be added to this Action space.)'
      ),
      clienttranslate('Also, take 2 Ore from the general supply for each Ore mine you have.'),
    ];

    $this->accumulation = [ORE => 1, STONE => 1];
  }

  public function getFlow($player, $dwarf)
  {
    return [
      'type' => NODE_SEQ,
      'childs' => [
        ['action' => COLLECT],
        [
          'action' => GAIN,
          'args' => [ORE => 2 * $player->countOreMines()],
        ],
      ],
    ];
  }
}
