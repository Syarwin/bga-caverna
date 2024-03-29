<?php
namespace CAV\ActionCards;

use CAV\Managers\Buildings;

class ActionExtension extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionExtension';
    $this->name = clienttranslate('Extension');
    $this->desc = [clienttranslate('or')];
    $this->tooltip = [
      clienttranslate(
        'You may either place a Meadow/Field twin tile on adjacent Forest spaces and take 1 Wood from the general supply'
      ),
      clienttranslate(
        'or you may place a Cavern/Tunnel twin tile on adjacent Mountain spaces and take 1 Stone from the general supply.'
      ),
    ];
    $this->players = [7];
  }

  protected function getFlow($player, $dwarf)
  {
    $flow = [
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

    $building = $player->hasPlayedBuilding('G_GuestRoom', false);
    if (!is_null($building) && $building->getExtraDatas('extension') != true) {
      $f = [
        'type' => NODE_XOR,
        'childs' => [$flow, $flow],
      ];
      $f['childs'][0]['type'] = NODE_SEQ;
      $f['childs'][0]['childs'][] = [
        'action' => \SPECIAL_EFFECT,
        'args' => ['cardType' => 'G_GuestRoom', 'method' => 'useExtension'],
      ];
      $flow = $f;
    }

    return $flow;
  }
}
