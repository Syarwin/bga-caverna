<?php
namespace CAV\ActionCards;

class ActionFirstPlayer extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionFirstPlayer';
    $this->name = clienttranslate('First Player');
    $this->tooltip = [
      clienttranslate(
        'Take the Starting player token and all the Food that has accumulated on this Action space. (1 Food will be added to this Action space every round)'
      ),
      clienttranslate('Additionally, take 2 Ore from the general supply.'),
    ];
    $this->players = [1, 2, 3];

    $this->accumulation = [FOOD => 1];
    $this->flow = [
      'type' => NODE_SEQ,
      'childs' => [
        ['action' => FIRSTPLAYER],
        ['action' => COLLECT],
        [
          'action' => GAIN,
          'args' => [ORE => 2],
        ],
      ],
    ];
  }

  public function canBeCopied($player, $dwarf, $ignoreResources = false)
  {
    return false;
  }
}
