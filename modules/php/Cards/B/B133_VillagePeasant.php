<?php
namespace AGR\Cards\B;

class B133_VillagePeasant extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B133_VillagePeasant';
    $this->name = ('Village Peasant');
    $this->deck = 'B';
    $this->number = 133;
    $this->category = POINTS_PROVIDER;
    $this->desc = [
      (
        'At the start of scoring, you get a number of vegetables equal to the smallest of the numbers of major improvements, minor improvements, and occupations you have.'
      ),
    ];
    $this->players = '3+';
    $this->implemented = false;
    $this->newSet = true;
  }
}
