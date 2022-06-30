<?php
namespace AGR\Cards\B;

class B75_WoodWorkshop extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B75_WoodWorkshop';
    $this->name = ('Wood Workshop');
    $this->deck = 'B';
    $this->number = 75;
    $this->category = BUILDING_RESOURCE_PROVIDER;
    $this->desc = [('Each time before you play or build an improvement, you get 1 wood.')];
    $this->cost = [
      CLAY => '1',
    ];
    $this->prerequisite = ('1 Occupation');
    $this->implemented = false;
  }
}
