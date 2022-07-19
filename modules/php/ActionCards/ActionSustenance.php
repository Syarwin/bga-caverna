<?php
namespace CAV\ActionCards;

class ActionSustenance extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionSustenance';
    $this->name = clienttranslate('Sustenance');
    $this->tooltip = [
      clienttranslate(
        'Take all the food markers that have accumulated on this Action space. (1 Food will be added to this Action space every round)'
      ),
      clienttranslate('Take 1 grain from the general supply.'),
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
    $this->players = [1, 2, 3];

    $this->accumulation = [FOOD => 1];
    $this->flow = [
      'type' => NODE_SEQ,
      'childs' => [
        ['action' => COLLECT],
        ['action' => GAIN, 'args' => [GRAIN => 1]],
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
