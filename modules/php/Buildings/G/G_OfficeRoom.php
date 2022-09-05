<?php
namespace CAV\Buildings\G;

class G_OfficeRoom extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_OfficeRoom';
    $this->category = 'dwelling';
    $this->name = clienttranslate('Office room');
    $this->desc = [clienttranslate('twin tiles may overhang;'), clienttranslate('every time you do so:')];
    $this->tooltip = [
      clienttranslate(
        'When placing twin tiles, you only need to place half of the tile on your Home board, the other half may overhang.'
      ),
      clienttranslate('Every time you do so, take 2 Gold from the general supply.'),
    ];
    $this->cost = [STONE => 1];
    $this->vp = 0;
  }

  public function isListeningTo($event)
  {
    return $this->isActionEvent($event, 'PlaceTile');
  }

  public function onPlayerAfterPlaceTile($player, $event)
  {
    $extended = false;
    foreach ($event['positions'] as $position) {
      if ($position['x'] == -1 || $position['x'] == 13 || $position['y'] == -1 || $position['y'] == 9) {
        $extended = true;
      }
    }
    if (
      $extended &&
      count($event['positions']) == 2 &&
      !in_array($event['tile'], [TILE_MINE_DEEP_TUNNEL, TILE_LARGE_PASTURE, TILE_MINE_DEEP_TUNNEL])
    ) {
      return $this->gainNode([GOLD => 2]);
    }
  }
}
