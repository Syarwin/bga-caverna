<?php
namespace AGR\Cards\B;

class B34_SpecialFood extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B34_SpecialFood';
    $this->name = ('Special Food');
    $this->deck = 'B';
    $this->number = 34;
    $this->category = POINTS_PROVIDER;
    $this->desc = [
      (
        'The next time you take animals from an accumulation space and accommodate all of them on your farm, you get 1 bonus point for each of these animals.'
      ),
    ];
    $this->prerequisite = ('No Animal');
    $this->implemented = false;
  }
}
