<?php
namespace AGR\Cards\D;

class D65_GrainSieve extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D65_GrainSieve';
    $this->name = ('Grain Sieve');
    $this->deck = 'D';
    $this->number = 65;
    $this->category = CROP_PROVIDER;
    $this->desc = [
      (
        'In the field phase of each harvest, if you harvest at least 2 grain, you get 1 additional grain from the general supply.'
      ),
    ];
    $this->cost = [
      WOOD => '1',
    ];
    $this->implemented = false;
  }
}
