<?php
namespace CAV\ActionCards;

class ActionLogging extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionLogging';
    $this->name = clienttranslate('Logging');
    $this->desc = [clienttranslate('and then / or')];
    $this->tooltip = [
      clienttranslate(
        'Take all the Wood that has accumulated on this Action space. (1 Wood will be added to this Action space every round unless it is empty. Then 3 Wood will be added to it instead.)'
      ),
      clienttranslate('Afterwards, you may undertake a Level 1 expedition if your Dwarf has a Weapon.'),
    ];
    $this->players = [1, 2, 3];

    $this->accumulation = [WOOD => [3, 1]];
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
