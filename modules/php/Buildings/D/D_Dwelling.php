<?php
namespace CAV\Buildings\D;

class D_Dwelling extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'D_Dwelling';
    $this->name = clienttranslate('Dwelling');
    $this->dwelling = 1;
    $this->nbInBox = 99;
    $this->desc = [clienttranslate('room for 1 dwarf')];
    $this->tooltip = [
      clienttranslate('The number of these ordinary Dwelling tiles is unlimited.'),
      clienttranslate('They provide room for exactly 1 Dwarf.'),
      clienttranslate(
        'If you use the "Furnish an ordinary dwelling for 2 Wood and 2 Stone" Expedition loot item (requires strength 12 or more), you can only build one of these Dwellings*.'
      ),
    ];
    $this->cost = [WOOD => 4, STONE => 3];
    $this->vp = 3;
  }
}
