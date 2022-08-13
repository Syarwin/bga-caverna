<?php
namespace CAV\Buildings\Y;

class Y_StateParlor extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_StateParlor';
    $this->category = 'foodBonus';
    $this->name = clienttranslate('State Parlor');
    $this->tooltip = [
      clienttranslate(
        'When building the State parlor, immediately (and only once) get 2 Food from the general supply for each Dwelling that is __(horizontally or vertically)__ adjacent to the State parlor.'
      ),
      clienttranslate(
        'When scoring, you will get 4 Bonus points for each Dwelling that is (horizontally or vertically) adjacent to the State parlor (i.e. at most 16 Bonus points). The entry-level room of your cave is also considered a Dwelling'
      ),
    ];
    $this->cost = [GOLD => 5, STONE => 3];
  }
}
