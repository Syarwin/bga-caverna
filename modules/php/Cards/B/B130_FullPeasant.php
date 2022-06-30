<?php
namespace AGR\Cards\B;

class B130_FullPeasant extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B130_FullPeasant';
    $this->name = ('Full Peasant');
    $this->deck = 'B';
    $this->number = 130;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      (
        'Each time after you use the "Grain Utilization" or "Fencing" action space while the other is unoccupied, you can pay 1 food to use the other space with the same person.'
      ),
    ];
    $this->players = '3+';
    $this->implemented = false;
  }
}
