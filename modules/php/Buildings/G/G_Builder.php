<?php
namespace CAV\Buildings\G;

class G_Builder extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_Builder';
    $this->category = 'material';
    $this->name = clienttranslate('Builder');
    $this->desc = [clienttranslate('you may replace 1 wood with 1 ore and/or 1 stone with 1 ore when paying for a furnishing tile')];
    $this->tooltip = [
      clienttranslate('You may replace 1 Wood with 1 Ore and/or 1 Stone with 1 Ore when paying for a Furnishing tile.'),
      clienttranslate('(For instance, you can build the Blacksmith for 2 Ore and 1 Stone.)'),
    ];
    $this->costs = [[STONE => 1]];
    $this->vp = 2;
  }
}
