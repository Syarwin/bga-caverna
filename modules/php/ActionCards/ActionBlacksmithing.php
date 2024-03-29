<?php
namespace CAV\ActionCards;

class ActionBlacksmithing extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionBlacksmithing';
    $this->name = clienttranslate('Blacksmithing');
    $this->desc = [clienttranslate('and then / or')];
    $this->tooltip = [
      clienttranslate(
        'If you use this Action space with an unarmed Dwarf, you may forge a Weapon for this Dwarf (maximum Weapon strength of 8) and then undertake
a Level 3 expedition.'
      ),
      clienttranslate('Instead, you may place an armed Dwarf on this Action space only to undertake the Expedition.'),
    ];

    $this->stage = 1;
  }

  protected function getFlow($player, $dwarf)
  {
    $armed = ($dwarf['weapon'] ?? 0) > 0;
    return $armed
      ? [
        'action' => EXPEDITION,
        'args' => [
          'lvl' => 3,
        ],
      ]
      : [
        'type' => NODE_SEQ,
        'childs' => [
          ['action' => BLACKSMITH],
          [
            'action' => EXPEDITION,
            'optional' => true,
            'args' => [
              'lvl' => 3,
            ],
          ],
        ],
      ];
  }
}
