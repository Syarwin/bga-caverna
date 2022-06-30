<?php
namespace AGR\Cards\B;

class B23_FinalScenario extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B23_FinalScenario';
    $this->name = ('Final Scenario');
    $this->deck = 'B';
    $this->number = 23;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      (
        'Place the action space card for round 14 face up in front of you. Only you can use it until it is placed on the game board.'
      ),
    ];
    $this->prerequisite = ('Round 13 or Before');
    $this->implemented = false;
  }
}
