<?php
namespace CAV\Buildings\G;
use CAV\Core\Helpers;
use CAV\Helpers\Utils;

class G_Builder extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_Builder';
    $this->category = 'material';
    $this->name = clienttranslate('Builder');
    $this->desc = [
      clienttranslate(
        'you may replace 1 <WOOD> with 1 <ORE> and/or 1 <STONE> with 1 <ORE> when paying for a furnishing tile'
      ),
    ];
    $this->tooltip = [
      clienttranslate('You may replace 1 Wood with 1 Ore and/or 1 Stone with 1 Ore when paying for a Furnishing tile.'),
      clienttranslate('(For instance, you can build the Blacksmith for 2 Ore and 1 Stone.)'),
    ];
    $this->cost = [STONE => 1];
    $this->vp = 2;
  }

  public function onPlayerComputeCostsFurnish($player, &$args)
  {
    Utils::addBonus($args['costs'], [WOOD => -1, ORE => 1], $this->id, true);
    Utils::addBonus($args['costs'], [STONE => -1, ORE => 1], $this->id, true);
  }
}
