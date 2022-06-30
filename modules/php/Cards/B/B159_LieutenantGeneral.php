<?php
namespace AGR\Cards\B;

class B159_LieutenantGeneral extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B159_LieutenantGeneral';
    $this->name = ('Lieutenant General');
    $this->deck = 'B';
    $this->number = 159;
    $this->category = FOOD_PROVIDER;
    $this->desc = [
      (
        'For each field tile that another player places next to an existing field tile, you get 1 food from the general supply. In round 14, you get 1 grain instead.'
      ),
    ];
    $this->players = '4+';
    $this->implemented = false;
  }
}
