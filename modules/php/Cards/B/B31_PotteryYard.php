<?php
namespace AGR\Cards\B;

class B31_PotteryYard extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B31_PotteryYard';
    $this->name = ('Pottery Yard');
    $this->deck = 'B';
    $this->number = 31;
    $this->category = POINTS_PROVIDER;
    $this->desc = [
      (
        'During the scoring, if there are at least 2 orthogonally adjacent unused spaces in your farm, you get 2 bonus points. (You still get the negative points for those unused spaces.'
      ),
    ];
    $this->vp = 1;
    $this->prerequisite = ('Pottery (or an Upgrade Thereof)');
    $this->implemented = false;
    $this->newSet = true;
  }
}
