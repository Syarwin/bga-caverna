<?php
namespace CAV\ActionCards;

class ActionSmallDriftMining extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionSmallDriftMining';
    $this->name = clienttranslate('Small-scale  drift Mining');
    $this->desc = [clienttranslate('and / or')];
    $this->tooltip = [
      clienttranslate(
        'You may place a Cavern/Tunnel twin tile on 2 adjacent empty Mountain spaces of your Home board.'
      ),
      clienttranslate(
        'If you place the twin tile on one of the underground water sources, you will immediately get 1 or 2 Food from the general supply.'
      ),
      clienttranslate('If you do, take 1 stone from the general supply'),
    ];
    $this->players = [5];

    $this->flow = [
      'type' => NODE_SEQ,
      'childs' => [
        [
          'action' => PLACE_TILE,
          'args' => [
            'tiles' => [TILE_TUNNEL_CAVERN],
          ],
        ],
        ['action' => GAIN, 'args' => [STONE => 1]],
      ],
    ];
  }
}
