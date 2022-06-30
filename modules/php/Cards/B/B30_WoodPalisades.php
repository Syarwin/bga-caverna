<?php
namespace AGR\Cards\B;

class B30_WoodPalisades extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B30_WoodPalisades';
    $this->name = ('Wood Palisades');
    $this->deck = 'B';
    $this->number = 30;
    $this->category = POINTS_PROVIDER;
    $this->desc = [
      (
        'Instead of a fence piece, you can place 2 wood from your supply on the fence spaces at the edge of your farmyard. These fence spaces with 2 wood are worth 1 bonus point.'
      ),
    ];
    $this->cost = [
      FOOD => '1',
    ];
    $this->implemented = false;
  }
}
