<?php
namespace CAV\ActionCards;

class ActionOreMining extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionOreMining';
    $this->name = clienttranslate('Ore Mining');
    $this->tooltip = [
      clienttranslate(
        'Take all the Ore that has accumulated on this Action space. (1 Ore will be added to this Action space every round unless it is empty. Then 2 Ore will be added to it instead.)'
      ),
      clienttranslate('Additionally, you may take 2 Ore from the general supply for each Ore mine you have.'),
    ];
    $this->players = [1, 2, 3];

    $this->accumulation = [ORE => [2, 1]];
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
