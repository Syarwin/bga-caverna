<?php
namespace AGR\Cards\D;

class D72_StableManure extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D72_StableManure';
    $this->name = ('Stable Manure');
    $this->deck = 'D';
    $this->number = 72;
    $this->category = CROP_PROVIDER;
    $this->desc = [
      (
        'In the field phase of each harvest, you can harvest 1 additional good from a number of fields equal to the number of unfenced stables you have.'
      ),
    ];
    $this->prerequisite = ('At Most 1 Occupation');
    $this->implemented = false;
  }
}
