<?php
namespace AGR\Cards\B;

class B3_Moonshine extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B3_Moonshine';
    $this->name = ('Moonshine');
    $this->deck = 'B';
    $this->number = 3;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      (
        'Randomly select an occupation in your hand. Either play it for an occupation cost of 2 food, or give it to the player to your left.'
      ),
    ];
    $this->passing = true;
    $this->implemented = false;
  }
}
