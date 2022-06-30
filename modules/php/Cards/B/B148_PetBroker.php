<?php
namespace AGR\Cards\B;

class B148_PetBroker extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B148_PetBroker';
    $this->name = ('Pet Broker');
    $this->deck = 'B';
    $this->number = 148;
    $this->category = FARM_PLANNER;
    $this->desc = [
      (
        'When you play this card, you immediately get 1 sheep. You can keep 1 sheep on each occupation in front of you.'
      ),
    ];
    $this->players = '4+';
    $this->implemented = false;
  }
}
