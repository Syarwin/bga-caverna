<?php
namespace CAV\ActionCards;

class ActionOreMining4 extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionOreMining4';
    $this->actionCardType = 'ActionOreMining';
    $this->name = clienttranslate('Ore Mining');
    $this->tooltip = [
      clienttranslate(
        'Take all the Ore that has accumulated on this Action space. (2 Ore will be added to this Action space every round unless it is empty. Then 3 Ore will be added to it instead.)'
      ),
      clienttranslate('Additionally, you may take 2 Ore from the general supply for each Ore mine you have.'),
    ];
    $this->players = [4, 5, 6, 7];

    $this->accumulation = [ORE => [3, 2]];
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
