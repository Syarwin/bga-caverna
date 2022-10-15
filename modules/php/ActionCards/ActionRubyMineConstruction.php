<?php
namespace CAV\ActionCards;

class ActionRubyMineConstruction extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionRubyMineConstruction';
    $this->name = clienttranslate('Ruby Mine Construction');
    $this->desc = [clienttranslate('or')];
    $this->tooltip = [
      clienttranslate('Place a Ruby mine on an empty Tunnel or Deep tunnel space of your cave system.'),
      clienttranslate(
        'If (and only if) you place the Ruby mine on a Deep tunnel, you may also take 1 Ruby from the general supply.'
      ),
      clienttranslate(
        '(Deep tunnels can only be found on Ore mine/Deep tunnel twin tiles. This is why the illustration of the action shows such a tile.)'
      ),
    ];

    $this->stage = 2;
  }

  protected function getFlow($player, $dwarf)
  {
    if ($player->hasPlayedBuilding('G_GuestRoom')) {
      return [
        'type' => NODE_OR,
        'childs' => [
          [
            'action' => PLACE_TILE,
            'args' => [
              'tiles' => [TILE_RUBY_MINE],
              'constraint' => TILE_TUNNEL,
            ],
          ],
          [
            'action' => PLACE_TILE,
            'args' => [
              'tiles' => [TILE_RUBY_MINE],
              'constraint' => TILE_DEEP_TUNNEL,
            ],
          ],
        ],
      ];
    } else {
      return [
        'action' => PLACE_TILE,
        'args' => [
          'tiles' => [TILE_RUBY_MINE],
        ],
      ];
    }
  }
}
