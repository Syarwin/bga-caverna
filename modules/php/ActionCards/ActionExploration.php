<?php
namespace CAV\ActionCards;

class ActionExploration extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionExploration';
    $this->name = clienttranslate('Exploration');
    $this->tooltip = [
      clienttranslate('You may only use this Action space with an armed Dwarf.'),
      clienttranslate(
        'You have to undertake a Level 4 expedition by choosing up to 4 different loot items with a Minimum strength value equal to or lower than the Weapon strength of your Dwarf (see page 21 of the rule book).'
      ),
      clienttranslate(
        '(This Action space card is removed in 2-player games. Consequently, in 2-player games, stage 3 only consists of two rounds.)'
      ),
    ];

    $this->stage = 3;
    $this->players = [3, 4, 5, 6, 7];
  }

  protected function getFlow($player, $dwarf)
  {
    return [
      'action' => \EXPEDITION,
      'args' => [
        'lvl' => 4,
      ],
    ];
  }
}
