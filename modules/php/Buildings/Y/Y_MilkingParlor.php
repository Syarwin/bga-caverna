<?php
namespace CAV\Buildings\Y;

class Y_MilkingParlor extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_MilkingParlor';
    $this->category = 'foodBonus';
    $this->name = clienttranslate('Milking Parlor');
    $this->tooltip = [
      clienttranslate(
        'When building the Milking parlor, immediately (and only once) get 1 Food from the general supply for each Cattle on your Home board.'
      ),
      clienttranslate(
        'When scoring, you will get 1 Bonus point for each Cattle on your Home board. __(You will get the usual points for “Farm animals and Dogs” regardless.)__'
      ),
    ];
    $this->cost = [WOOD => 2, STONE => 2];
  }
}
