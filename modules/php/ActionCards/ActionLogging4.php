<?php
namespace CAV\Cards\ActionCards;

class ActionLogging4 extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionLogging4';
    $this->name = clienttranslate('Logging');
    $this->tooltip = [
      clienttranslate(
        'Take all the Wood that has accumulated on this Action space. (3 Wood will be added to this Action space every round.)'
      ),
      clienttranslate('Afterwards, you may undertake a Level 1 expedition if your Dwarf has a Weapon.'),
    ];
    $this->players = [1, 2, 3];

    $this->accumulation = [WOOD => 3];
    $this->flow = [
      'type' => SEQ_NODE,
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
