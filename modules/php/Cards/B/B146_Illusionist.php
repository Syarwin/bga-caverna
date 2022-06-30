<?php
namespace AGR\Cards\B;

class B146_Illusionist extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B146_Illusionist';
    $this->name = ('Illusionist');
    $this->deck = 'B';
    $this->number = 146;
    $this->category = BUILDING_RESOURCE_PROVIDER;
    $this->desc = [
      (
        'Each time you use a building resource accumulation space, you can discard exactly 1 card from your hand to get 1 additional building resource of the accumulating type.'
      ),
    ];
    $this->players = '3+';
    $this->implemented = false;
  }
}
