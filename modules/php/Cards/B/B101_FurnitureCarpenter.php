<?php
namespace AGR\Cards\B;

class B101_FurnitureCarpenter extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B101_FurnitureCarpenter';
    $this->name = ('Furniture Carpenter');
    $this->deck = 'B';
    $this->number = 101;
    $this->category = POINTS_PROVIDER;
    $this->desc = [
      (
        'Each harvest, if any player (including you) owns the Joinery or an upgrade thereof, you can buy exactly 1 bonus point for 2 food.'
      ),
    ];
    $this->players = '1+';
    $this->implemented = false;
    $this->newSet = true;
  }
}
