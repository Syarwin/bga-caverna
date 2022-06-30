<?php
namespace AGR\Cards\B;

class B76_Ceilings extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B76_Ceilings';
    $this->name = ('Ceilings');
    $this->deck = 'B';
    $this->number = 76;
    $this->category = BUILDING_RESOURCE_PROVIDER;
    $this->desc = [
      (
        'Place 1 wood on the next 5 round spaces. At the start of these rounds, you get the wood. Remove the wood promised by this card from future round spaces the next time you renovate.'
      ),
    ];
    $this->cost = [
      CLAY => '1',
    ];
    $this->prerequisite = ('1 Occupation');
    $this->implemented = false;
  }
}
