<?php
namespace CAV\ActionCards;

class ActionClearing4 extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionClearing4';
    $this->actionCardType = 'Clearing';
    $this->name = clienttranslate('Clearing');
    $this->desc = [clienttranslate('and / or')];
    $this->tooltip = [
      clienttranslate(
        'Take all the Wood that has accumulated on this Action space. (2 Wood will be added to this Action space every round)'
      ),
      clienttranslate(
        'Additionally, you may place a Meadow/Field twin tile on 2 adjacent empty Forest spaces of your Home board that are not covered by any tiles.'
      ),
      clienttranslate('If you place the twin tile on the small river, you will get 1 Food from the general supply.'),
      clienttranslate(
        'If you place the twin tile on one of the Wild boar preserves, you will get 1 Wild boar from the general supply.'
      ),
      clienttranslate(
        'The first tile that you place in the game must be placed adjacent to the cave entrance. Subsequent tiles must be placed adjacent to other Fields, Meadows or Pastures.'
      ),
    ];
    $this->players = [4, 5, 6, 7];

    $this->accumulation = [WOOD => 2];
    $this->flow = [
      'type' => NODE_SEQ,
      'childs' => [
        ['action' => COLLECT],
        [
          'action' => PLACE_TILE,
          'optional' => true,
          'args' => [
            'tiles' => [TILE_MEADOW_FIELD],
          ],
        ],
      ],
    ];
  }
}
