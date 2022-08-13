<?php
namespace CAV\Buildings\Y;

class Y_WeavingParlor extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_WeavingParlor';
    $this->category = 'foodBonus';
    $this->name = clienttranslate('Weaving Parlor');
    $this->tooltip = [
      clienttranslate(
        'When building the Weaving parlor, immediately (and only once) get 1 Food from the general supply for each Sheep on your Home board'
      ),
      clienttranslate(
        'When scoring, you will get 1 Bonus point for every 2 Sheep on your Home board. __(For instance, you will get 1/2/3/… Bonus points for 2-3/4-5/6-7/… Sheep, respectively. You will get the usual points for “Farm animals and Dogs” regardless.)__'
      ),
    ];
    $this->cost = [WOOD => 2, STONE => 1];
  }
}
