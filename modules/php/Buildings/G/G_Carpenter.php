<?php
namespace CAV\Buildings\G;

class G_Carpenter extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_Carpenter';
    $this->category = 'material';
    $this->name = clienttranslate('Carpenter');
    $this->desc = [clienttranslate('everytime you furnish a cavern or build fences, you receive a discount of')];
    $this->tooltip = [
      clienttranslate('Every time you furnish a Cavern or carry out a Build fences action, you may pay 1 fewer Wood.'),
    ];
    $this->cost = [STONE => 1];
    $this->vp = 0;
  }

  public function onPlayerComputeCostsFurnish($player, &$args)
  {
    Utils::addBonus($args['costs'], [WOOD => -1], $this->id);
  }

  // TODO : listen to placeTiles cost and check type of tile
}
