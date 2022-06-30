<?php
namespace AGR\Cards\B;

class B120_Sweep extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B120_Sweep';
    $this->name = ('Sweep');
    $this->deck = 'B';
    $this->number = 120;
    $this->category = BUILDING_RESOURCE_PROVIDER;
    $this->desc = [
      (
        'Each time before you use the action space card left of the card that has been most recently placed on a round space, you get 2 clay.'
      ),
    ];
    $this->players = '1+';
    $this->implemented = false;
  }
}
