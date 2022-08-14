<?php
namespace CAV\ActionCards;

class ActionLogging4 extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionLogging4';
    $this->actionCardType = 'Logging';
    $this->name = clienttranslate('Logging');
    $this->desc = [clienttranslate('and then / or')];
    $this->tooltip = [
      clienttranslate(
        'Take all the Wood that has accumulated on this Action space. (3 Wood will be added to this Action space every round.)'
      ),
      clienttranslate('Afterwards, you may undertake a Level 1 expedition if your Dwarf has a Weapon.'),
    ];
    $this->players = [4, 5, 6, 7];

    $this->accumulation = [WOOD => 3];
    $this->flow = [
      'type' => NODE_SEQ,
      'childs' => [
        ['action' => COLLECT],
        [
          'action' => EXPEDITION,
          'optional' => true,
          'args' => [
            'lvl' => 1,
          ],
        ],
      ],
    ];
  }
}
