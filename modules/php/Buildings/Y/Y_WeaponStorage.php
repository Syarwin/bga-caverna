<?php
namespace CAV\Buildings\Y;

class Y_WeaponStorage extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_WeaponStorage';
    $this->category = 'bonus';
    $this->name = clienttranslate('Weapon Storage');
    $this->tooltip = [
      clienttranslate(
        'When scoring, the Weapon storage will be worth 3 Bonus points for each armed Dwarf you have __(regardless of its Weapon strength)__.'
      ),
    ];
    $this->cost = [WOOD => 3, STONE => 2];
  }
}
