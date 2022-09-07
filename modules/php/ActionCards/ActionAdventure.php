<?php
namespace CAV\ActionCards;

class ActionAdventure extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionAdventure';
    $this->name = clienttranslate('Adventure');
    $this->desc = [clienttranslate('and / or')];
    $this->tooltip = [
      clienttranslate(
        'If you use this Action space with an unarmed Dwarf, you may forge a new Weapon for this Dwarf (maximum Weapon strength of 8) and then undertake two Level 1 expeditions, one after another (see page 21 of the rule book).'
      ),
      clienttranslate(
        'Instead, you may place an armed Dwarf on this Action space only to undertake the two Expeditions.'
      ),
    ];

    $this->stage = 4;
  }

  protected function getFlow($player, $dwarf)
  {
    $childs = [];

    $armed = ($dwarf['weapon'] ?? 0) > 0;
    if (!$armed) {
      $childs[] = ['action' => BLACKSMITH];
    }
    $childs[] = [
      'action' => EXPEDITION,
      'optional' => !$armed,
      'args' => [
        'lvl' => 1,
      ],
    ];
    $childs[] = [
      'action' => EXPEDITION,
      'optional' => true,
      'args' => [
        'lvl' => 1,
      ],
    ];

    return [
      'type' => NODE_SEQ,
      'childs' => $childs,
    ];
  }
}
