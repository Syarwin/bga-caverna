<?php
namespace AGR\Cards\B;

class B129_Seatmate extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B129_Seatmate';
    $this->name = ('Seatmate');
    $this->deck = 'B';
    $this->number = 129;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      (
        'You can use the action space on round space 13 even if it is occupied by one or more people of the players to your immediate left and right.'
      ),
    ];
    $this->players = '3+';
    $this->implemented = false;
  }
}
