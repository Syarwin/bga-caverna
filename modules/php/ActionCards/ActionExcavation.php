<?php
namespace CAV\ActionCards;

class ActionExcavation extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionExcavation';
    $this->name = clienttranslate('Excavation');
    $this->desc = [clienttranslate('and / or')];
    $this->tooltip = [
      clienttranslate(
        'Take all the Stone that has accumulated on this Action space. (1 Stone will be added to this Action space every round)'
      ),
      clienttranslate(
        'Additionally, you may place a Cavern/Tunnel or a Cavern/Cavern twin tile on 2 adjacent empty Mountain spaces of your Home board.'
      ),
      clienttranslate(
        'If you place the twin tile on one of the underground water sources, you will immediately get 1 or 2 Food from the general supply.'
      ),
      clienttranslate(
        'You have to place the twin tile adjacent to an already occupied Mountain space, i.e. you have to extend your cave system.'
      ),
    ];
    $this->players = [1, 2, 3];

    $this->accumulation = [STONE => 1];
    $this->flow = [
      'type' => NODE_SEQ,
      'childs' => [
        ['action' => COLLECT],
        [
          'action' => PLACE_TILE,
          'optional' => true,
          'args' => [
            'tiles' => [TILE_TUNNEL_CAVERN, TILE_CAVERN_CAVERN],
          ],
        ],
      ],
    ];
  }
}
