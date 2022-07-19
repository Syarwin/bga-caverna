<?php
namespace CAV\ActionCards;

class ActionRubyMining extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionRubyMining';
    $this->name = clienttranslate('Ruby Mining');
    $this->tooltip = [
      clienttranslate(
        'Take all the Ruby that has accumulated on this Action space. (1 Ruby will be added to this Action space every round.)'
      ),
      clienttranslate('Take one more Ruby from the general supply if you have at least one Ruby mine.'),
    ];
    $this->players = [1, 2, 3, 4, 5, 6, 7];

    $this->accumulation = [RUBY => 1];
  }

  public function getFlow($player, $dwarf)
  {
    return $player->countRubyMines() > 0
      ? [
        'type' => NODE_SEQ,
        'childs' => [
          ['action' => COLLECT],
          [
            'action' => GAIN,
            'args' => [RUBY => 1],
          ],
        ],
      ]
      : ['action' => COLLECT];
  }
}
