<?php

namespace CAV\Buildings\D;

class D_CoupleDwelling extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'D_CoupleDwelling';
    $this->category = 'dwelling';
    $this->name = clienttranslate('Couple Dwelling');
    $this->desc = [clienttranslate('room for 2 dwarfs')];
    $this->tooltip = [
      clienttranslate(
        'The Couple dwelling provides room for 2 Dwarfs. You can get these Dwarfs one at a time with a Family growth action. '
      ),
      clienttranslate(
        '(Even if you build this Furnishing tile on the “Urgent wish for children” Action space, you may only grow your family once with that action.)'
      ),
    ];
    $this->dwelling = 2;
    $this->cost = [WOOD => 8, STONE => 6];
    $this->vp = 5;
  }

  public function isConsideredDwelling()
  {
    return true;
  }
}
