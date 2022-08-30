<?php
namespace CAV\Buildings\G;

use CAV\Helpers\Utils;

class G_StoneCarver extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_StoneCarver';
    $this->category = 'material';
    $this->name = clienttranslate('Stone Carver');
    $this->desc = [
      clienttranslate('immediately'),
      clienttranslate('every time you furnish a cavern or build a stable, you receive a discount of'),
    ];
    $this->tooltip = [
      clienttranslate(
        'When building the Stone carver, immediately (and only once) get 2 Stone from the general supply.'
      ),
      clienttranslate('Every time you furnish a Cavern or build a Stable, you may pay 1 fewer Stone.'),
      clienttranslate('(Consequently, you can build Stables for free.)'),
    ];
    $this->cost = [WOOD => 1];
    $this->vp = 1;
    $this->beginner = true;
  }

  protected function onBuy($player, $eventData)
  {
    return $this->gainNode([STONE => 2]);
  }

  public function onPlayerComputeCostsFurnish($player, &$args)
  {
    Utils::addBonus($args['costs'], [STONE => -1], $this->id);
  }

  public function onPlayerComputeCostsStables($player, &$args)
  {
    Utils::addBonus($args['costs'], [STONE => -1], $this->id);
  }
}
