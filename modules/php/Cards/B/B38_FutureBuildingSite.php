<?php
namespace AGR\Cards\B;

class B38_FutureBuildingSite extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B38_FutureBuildingSite';
    $this->name = ('Future Building Site');
    $this->deck = 'B';
    $this->number = 38;
    $this->category = POINTS_PROVIDER;
    $this->desc = [
      (
        'Up until all other farmyard spaces are used, you cannot use the unused spaces that are orthogonally adjacent to your house.'
      ),
    ];
    $this->vp = 3;
    $this->prerequisite = ('Play in Round 4 or Before');
    $this->implemented = false;
  }
}
