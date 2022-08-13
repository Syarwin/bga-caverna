<?php
namespace CAV\ActionCards;

class ActionOreMineConstruction extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionOreMineConstruction';
    $this->name = clienttranslate('Ore Mine Construction');
    $this->desc = [clienttranslate('and then / or')];
    $this->tooltip = [
      clienttranslate(
        'If you have 2 horizontally or vertically adjacent Tunnel spaces in your Mountain, you may place an Ore mine/Deep tunnel twin tile on those spaces and take 3 Ore from the general supply.'
      ),
      clienttranslate('(You do not get any Ore unless you place the twin tile in your Mountain.)'),
      clienttranslate('The twin tile may only be placed on ordinary Tunnel spaces but not on Deep tunnel spaces.'),
      clienttranslate(
        'Additionally or alternatively, you may undertake a Level 2 expedition if your Dwarf has a Weapon.'
      ),
    ];

    $this->stage = 1;
  }

  protected function getFlow($player, $dwarf)
  {
    $childs = [
      [
        'type' => NODE_SEQ,
        'childs' => [
          [
            'action' => PLACE_TILE,
            'args' => [
              'tiles' => [TILE_MINE_DEEP_TUNNEL],
            ],
          ],
          [
            'action' => GAIN,
            'args' => [ORE => 3],
          ],
        ],
      ],
    ];

    if (($dwarf['weapon'] ?? 0) > 0) {
      $childs[] = [
        'action' => EXPEDITION,
        'args' => [
          'lvl' => 2,
        ],
      ];
    }

    return [
      'type' => NODE_THEN_OR,
      'childs' => $childs,
    ];
  }
}
