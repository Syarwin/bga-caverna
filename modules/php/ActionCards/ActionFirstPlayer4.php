<?php
namespace CAV\Cards\ActionCards;

class ActionFirstPlayer4 extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionFirstPlayer4';
    $this->actionCardType = 'ActionFirstPlayer';
    $this->name = clienttranslate('First Player');
    $this->tooltip = [
      clienttranslate(
        'Take the Starting player token and all the Food that has accumulated on this Action space. (1 Food will be added to this Action space every round unless there is no Stone on it. Then 2 Stone will be added to it instead.)'
      ),
      clienttranslate('Additionally, take 1 Ruby from the general supply.'),
    ];
    $this->players = [4, 5, 6, 7];

    $this->accumulation = [FOOD => 1];
    $this->flow = [
      'type' => NODE_SEQ,
      'childs' => [
        ['action' => FIRSTPLAYER],
        ['action' => COLLECT],
        [
          'action' => GAIN,
          'args' => [RUBY => 1],
        ],
      ],
    ];
  }
}
