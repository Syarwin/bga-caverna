<?php
namespace AGR\Cards\B;

class B35_HookKnife extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B35_HookKnife';
    $this->name = ('Hook Knife');
    $this->deck = 'B';
    $this->number = 35;
    $this->category = POINTS_PROVIDER;
    $this->desc = [
      (
        'Once this game, when you have 9/8/7/6/5/5 sheep on your farm in a 1-/2-3-/4-/5-/6- player game, you immediately get 2 bonus points.'
      ),
    ];
    $this->cost = [
      WOOD => '1',
    ];
    $this->implemented = false;
    $this->newSet = true;
  }
}
