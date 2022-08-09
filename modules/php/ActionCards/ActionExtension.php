<?php
namespace CAV\ActionCards;

class ActionExtension extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionExtension';
    $this->name = clienttranslate('Extension');
    $this->desc = [clienttranslate('either')];
    $this->tooltip = [
      clienttranslate(
        'You may either place a Meadow/Field twin tile on adjacent Forest spaces and take 1 Wood from the general supply'
      ),
      clienttranslate(
        'or you may place a Cavern/Tunnel twin tile on adjacent Mountain spaces and take 1 Stone from the general supply.'
      ),
    ];
    $this->players = [7];

    $this->flow = [
      'type' => NODE_XOR,
      'childs' => [
        [
          'type' => NODE_SEQ,
          'childs' => [
            [
              'action' => PLACE_TILE,
              'args' => [
                'tiles' => [TILE_MEADOW_FIELD],
              ],
            ],
            ['action' => GAIN, 'args' => [WOOD => 1]],
          ],
        ],
        [
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
        ],
      ],
    ];
  }
}
