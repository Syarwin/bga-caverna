<?php
namespace AGR\Cards\D;

class D81_RoofLadder extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D81_RoofLadder';
    $this->name = ('Roof Ladder');
    $this->deck = 'D';
    $this->number = 81;
    $this->category = BUILDING_RESOURCE_PROVIDER;
    $this->desc = [
      ('Each time you renovate, you pay 1 fewer reed and, at the end of the action, you get 1 stone.'),
    ];
    $this->cost = [
      WOOD => '1',
    ];
    $this->implemented = false;
  }
}
