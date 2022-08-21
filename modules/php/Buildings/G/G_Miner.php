<?php
namespace CAV\Buildings\G;

class G_Miner extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_Miner';
    $this->category = 'material';
    $this->name = clienttranslate('Miner');
    $this->desc = [
      clienttranslate('at the beginning of each round'),
      clienttranslate('per'),
      clienttranslate('in an ore mine'),
    ];
    $this->tooltip = [
      clienttranslate(
        'At the beginning of each round, you will get 1 Ore from the general supply for each Ore mine holding a Donkey.'
      ),
      clienttranslate('(This does not apply to Ruby mines with Donkeys.).'),
    ];
    $this->cost = [WOOD => 1, STONE => 1];
    $this->vp = 3;
  }

  public function isListeningTo($event)
  {
    return $this->isPlayerEvent($event) && $event['type'] == 'StartOfTurn';
  }

  public function onPlayerStartOfTurn($player, $event)
  {
    $donkeys = $player->countAnimalsOnTile(\TILE_ORE_MINE, DONKEY);

    if ($donkeys > 0) {
      return $this->gainNode([ORE => $donkeys]);
    }
  }
}
