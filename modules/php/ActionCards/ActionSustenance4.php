<?php
namespace CAV\ActionCards;

class ActionSustenance4 extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionSustenance4';
    $this->actionCardType = 'ActionSustenance';
    $this->name = clienttranslate('Sustenance');
    $this->tooltip = [
      clienttranslate(
        'Take all the goods that have accumulated on this Action space. (1 Vegetable will be added to it every round unless it is empty. Then 1 Grain will be added to it instead.)'
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

    $this->accumulation = [VEGETABLE => [0, 1], GRAIN => [1, 0]];
    $this->flow = [
      'type' => NODE_SEQ,
      'childs' => [
        ['action' => COLLECT],
        [
          'action' => CONSTRUCT,
          'optional' => true,
          'args' => [
            'tiles' => [TILE_MEADOW_FIELD],
          ],
        ],
      ],
    ];
  }
}
