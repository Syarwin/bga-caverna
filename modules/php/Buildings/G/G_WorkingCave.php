<?php
namespace CAV\Buildings\G;

class G_WorkingCave extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_WorkingCave';
    $this->category = 'food';
    $this->name = clienttranslate('Working Cave');
    $this->desc = [clienttranslate('you may feed exactly 1 Dwarf with')];
    $this->tooltip = [
      clienttranslate(
        'Every time you have to feed your Dwarfs at the end of a round __(including the special Feeding phases)__, you may feed exactly one of them with 1 Wood or 1 Stone or 2 Ore'
      ),
    ];
    $this->cost = [STONE => 1, WOOD => 1];
    $this->vp = 2;
  }
}
