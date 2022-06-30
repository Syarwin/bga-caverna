<?php
namespace AGR\Cards\D;

class D76_SocialBenefits extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D76_SocialBenefits';
    $this->name = ('Social Benefits');
    $this->deck = 'D';
    $this->number = 76;
    $this->category = BUILDING_RESOURCE_PROVIDER;
    $this->desc = [
      (
        'Immediately after the feeding phase of each harvest, if you have no food left, you get 1 wood and 1 clay.'
      ),
    ];
    $this->cost = [
      REED => '1',
    ];
    $this->prerequisite = ('At Most 1 Occupation');
    $this->implemented = false;
    $this->newSet = true;
  }
}
