<?php
namespace AGR\Cards\B;

class B81_Handcart extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B81_Handcart';
    $this->name = ('Handcart');
    $this->deck = 'B';
    $this->number = 81;
    $this->category = BUILDING_RESOURCE_PROVIDER;
    $this->desc = [
      (
        'Before each work phase, you can take 1 building resource from at most one wood/clay/reed/stone accumulation space containing at least 6/5/4/4 building resources of the same type.'
      ),
    ];
    $this->cost = [
      WOOD => '1',
    ];
    $this->implemented = false;
  }
}
