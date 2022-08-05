<?php
namespace CAV\ActionCards;

class ActionDriftMining4 extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionDriftMining4';
    $this->actionCardType = 'ActionDriftMining';
    $this->name = clienttranslate('Drift Mining');
    $this->desc = [clienttranslate('and / or')];
    $this->tooltip = [
      clienttranslate(
        'Take all the Stone that has accumulated on this Action space. (2 Stone will be added to this Action space every round)'
      ),
      clienttranslate(
        'Additionally, you may place a Cavern/Tunnel twin tile on 2 adjacent empty Mountain spaces of your Home board.'
      ),
      clienttranslate(
        'If you place the twin tile on one of the underground water sources, you will immediately get 1 or 2 Food from the general supply.'
      ),
      clienttranslate(
        'You have to place the twin tile adjacent to an already occupied Mountain space, i.e. you have to extend your cave system.'
      ),
    ];
    $this->players = [4, 5, 6, 7];

    $this->accumulation = [STONE => 2];
    $this->flow = [
      'type' => NODE_SEQ,
      'childs' => [
        ['action' => COLLECT],
        [
          'action' => CONSTRUCT,
          'optional' => true,
          'args' => [
            'tiles' => [TILE_CAVERN_TUNNEL],
          ],
        ],
      ],
    ];
  }
}
