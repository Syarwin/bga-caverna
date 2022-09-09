<?php
namespace CAV\Buildings\G;

use CAV\Helpers\Utils;

class G_Carpenter extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_Carpenter';
    $this->category = 'material';
    $this->name = clienttranslate('Carpenter');
    $this->desc = [clienttranslate('everytime you furnish a cavern or build fences, you receive a discount of 1 <WOOD>')];
    $this->tooltip = [
      clienttranslate('Every time you furnish a Cavern or carry out a Build fences action, you may pay 1 fewer Wood.'),
    ];
    $this->cost = [STONE => 1];
    $this->vp = 0;
    $this->beginner = true;
  }

  public function onPlayerComputeCostsFurnish($player, &$args)
  {
    Utils::addBonus($args['costs'], [WOOD => -1], $this->id);
  }

  public function onPlayerComputePlaceTileCosts($player, &$args)
  {
    if (
      isset($args['tiles']) &&
      (in_array(TILE_PASTURE, $args['tiles']) || in_array(TILE_LARGE_PASTURE, $args['tiles']))
    ) {
      Utils::addBonus($args['costs'], [WOOD => -1], $this->id);
    }
  }
}
