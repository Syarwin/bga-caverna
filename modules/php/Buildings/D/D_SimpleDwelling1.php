<?php
namespace CAV\Buildings\D;

class D_SimpleDwelling1 extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'D_SimpleDwelling1';
    $this->category = 'dwelling';
    $this->name = clienttranslate('Simple Dwelling');
    $this->desc = [clienttranslate('room for 1 dwarf')];
    $this->tooltip = [
      clienttranslate(
        'The Simple dwellings are cheaper than the ordinary Dwellings by 1 building material (here: 1 Stone) but therefore they are not worth any Gold points.'
      ),
      clienttranslate('They provide room for exactly 1 Dwarf'),
    ];
    $this->dwelling = 1;
    $this->cost = [WOOD => 4, STONE => 2];
    $this->beginner = true;
  }
}
