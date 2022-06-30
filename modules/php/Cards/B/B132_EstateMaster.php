<?php
namespace AGR\Cards\B;

class B132_EstateMaster extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B132_EstateMaster';
    $this->name = ('Estate Master');
    $this->deck = 'B';
    $this->number = 132;
    $this->category = POINTS_PROVIDER;
    $this->desc = [
      (
        'Once you have no unused farmyard spaces left, you get 1 bonus point for each vegetable that you harvest.'
      ),
    ];
    $this->players = '3+';
    $this->implemented = false;
  }
}
