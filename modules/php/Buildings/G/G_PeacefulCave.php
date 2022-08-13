<?php
namespace CAV\Buildings\G;

class G_PeacefulCave extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_PeacefulCave';
    $this->category = 'food';
    $this->name = clienttranslate('Peaceful Cave');
    $this->desc = [clienttranslate('you may trade your Weapons for Food at a 1:1 ration according to their strength')];
    $this->tooltip = [
      clienttranslate(
        'At any time, you can trade the Weapons of your Dwarfs for Food. You get a number of Food equal to the strength of the Weapon you trade in. You can trade multiple Weapons at the same time or at different points in time.'
      ),
      clienttranslate(
        '__(For instance, if you traded in a Weapon of strength 14, you would get 14 Food from the general supply. The Peaceful cave works well with the Prayer chamber.)__'
      ),
    ];
    $this->cost = [STONE => 2, WOOD => 2];
    $this->vp = 2;
  }
}
