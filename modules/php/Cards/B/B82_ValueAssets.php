<?php
namespace AGR\Cards\B;

class B82_ValueAssets extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B82_ValueAssets';
    $this->name = ('Value Assets');
    $this->deck = 'B';
    $this->number = 82;
    $this->category = BUILDING_RESOURCE_PROVIDER;
    $this->desc = [
      (
        'After each harvest, you can buy exactly one of the following goods: 1 Food → 1 Wood; 1 Food → 1 Clay; 2 Food → 1 Reed; 2 Food → 1 Stone'
      ),
    ];
    $this->implemented = false;
    $this->newSet = true;
  }
}
