<?php
namespace CAV\ActionCards;

class ActionSlash extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionSlash';
    $this->name = clienttranslate('Slash-and-burn');
    $this->desc = [clienttranslate('and then / or')];
    $this->tooltip = [
      clienttranslate(
        'Place a Meadow/Field twin tile on 2 adjacent empty Forest spaces of your Home board that are not covered by any tiles.'
      ),
      clienttranslate('If you place the twin tile on the small river, you will get 1 Food from the general supply.'),
      clienttranslate(
        'If you place the twin tile on one of the Wild boar preserves, you will get 1 Wild boar from the general supply.'
      ),
      clienttranslate(
        'The first tile that you place in the game must be placed adjacent to the cave entrance. Subsequent tiles must be placed adjacent to other Fields, Meadows or Pastures.'
      ),
      clienttranslate(
        'Afterwards, you may carry out a Sow action to sow up to 2 new Grain and/or up to 2 new Vegetable fields (as usual).'
      ),
    ];

    $this->flow = [
      'type' => NODE_SEQ,
      'childs' => [
        [
          'action' => PLACE_TILE,
          'args' => [
            'tiles' => [TILE_MEADOW_FIELD],
          ],
        ],
        [
          'action' => SOW,
          'optional' => true,
        ],
      ],
    ];
  }
}
