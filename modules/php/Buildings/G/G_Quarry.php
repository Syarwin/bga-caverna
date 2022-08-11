<?php
namespace CAV\Buildings\G;

class G_Quarry extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_Quarry';
    $this->category = 'material';
    $this->name = clienttranslate('Quarry');
    $this->desc = [clienttranslate('for each newborn')];
    $this->tooltip = [
      clienttranslate('From now on, you will immediately get 1 Stone from the general supply for each newborn Donkey.'),
      clienttranslate('(This does not apply to Donkeys that you get from game boards or for Rubies.)'),
    ];
    $this->cost = [WOOD => 1];
    $this->vp = 2;
  }
}
