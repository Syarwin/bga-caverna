<?php
namespace AGR\Cards\B;

class B117_Informant extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B117_Informant';
    $this->name = ('Informant');
    $this->deck = 'B';
    $this->number = 117;
    $this->category = BUILDING_RESOURCE_PROVIDER;
    $this->desc = [
      (
        'When you play this card, you immediately get 1 wood. After each work phase, if you have more stone than clay in your supply, you get 1 wood.'
      ),
    ];
    $this->players = '1+';
    $this->implemented = false;
    $this->newSet = true;
  }
}
