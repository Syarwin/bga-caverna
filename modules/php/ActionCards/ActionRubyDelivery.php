<?php
namespace CAV\ActionCards;

class ActionRubyDelivery extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionRubyDelivery';
    $this->name = clienttranslate('Ruby Delivery');
    $this->desc = [clienttranslate('if you have at least')];
    $this->tooltip = [
      clienttranslate(
        'Take all the Rubies that have accumulated on this Action space. (Every round, 1 Ruby will be added to this Action space unless it is empty. Then 2 Rubies will be added to it instead.)'
      ),
      clienttranslate('Take one more Ruby from the general supply if you have at least two Ruby mines.'),
    ];

    $this->stage = 4;
    $this->accumulation = [RUBY => [2, 1]];
  }

  public function getFlow($player, $dwarf)
  {
    return $player->countRubyMines() > 1
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
