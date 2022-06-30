<?php
namespace AGR\Cards\B;

class B26_AgrarianFences extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B26_AgrarianFences';
    $this->name = ('Agrarian Fences');
    $this->deck = 'B';
    $this->number = 26;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      (
        'Each time you use the "Grain Utilization" action space, you can take a "Build Fences" action instead of one of the two actions provide by the action space.'
      ),
    ];
    $this->implemented = false;
  }
}
