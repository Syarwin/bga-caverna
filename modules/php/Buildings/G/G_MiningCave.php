<?php
namespace CAV\Buildings\G;

class G_MiningCave extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_MiningCave';
    $this->category = 'food';
    $this->name = clienttranslate('Mining Cave');
    $this->desc = [clienttranslate('reduces feeding costs by'), clienttranslate('per'), clienttranslate('in a Mine')];
    $this->tooltip = [
      clienttranslate(
        'Every time you have to feed your Dwarfs at the end of a round __(including the special Feeding phases)__, the total feeding costs will be reduced by 1 Food per Donkey in an __(Ore or Ruby)__ mine'
      ),
    ];
    $this->cost = [STONE => 2, WOOD => 3];
    $this->vp = 2;
  }
}
