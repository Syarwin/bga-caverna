<?php

namespace CAV\ActionCards;

use CAV\Helpers\Utils;

class ActionFenceBuilding extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionFenceBuilding';
    $this->name = clienttranslate('Fence Building');
    $this->desc = [clienttranslate('and then / or')];
    $this->tooltip = [
      clienttranslate('Take all the Wood that has accumulated on this Action space'),
      clienttranslate('(Every round, 1 wood will be added to this Action space.)'),
      clienttranslate('Afterwards you may build a Small pasture or/and a Large pasture.'),
      clienttranslate('Pay 2 Wood for a Small pasture, 4 Wood for a Large pasture.'),
    ];

    $this->accumulation = [WOOD => 1];
    $this->players = [5];
  }

  protected function getFlow($player, $dwarf)
  {
    return [
      'type' => NODE_SEQ,
      'childs' => [
        ['action' => COLLECT],
        [
          'type' => NODE_OR,
          'optional' => true,
          'childs' => [
            [
              'action' => PLACE_TILE,
              'args' => ['tiles' => [TILE_PASTURE], 'costs' => [WOOD => 2]],
            ],
            [
              'action' => PLACE_TILE,
              'args' => ['tiles' => [TILE_LARGE_PASTURE], 'costs' => [WOOD => 4]],
            ],
          ],
        ],
      ],
    ];
  }
}
