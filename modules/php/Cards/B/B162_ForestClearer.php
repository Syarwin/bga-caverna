<?php
namespace AGR\Cards\B;

class B162_ForestClearer extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B162_ForestClearer';
    $this->name = ('Forest Clearer');
    $this->deck = 'B';
    $this->number = 162;
    $this->category = BUILDING_RESOURCE_PROVIDER;
    $this->desc = [
      (
        'Each time you obtain exactly 2/3/4 wood from a wood accumulation space, you get 1 additional wood and 1/0/1 food.'
      ),
    ];
    $this->players = '4+';
    $this->implemented = false;
  }
}
