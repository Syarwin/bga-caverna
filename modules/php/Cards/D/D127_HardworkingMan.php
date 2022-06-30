<?php
namespace AGR\Cards\D;

class D127_HardworkingMan extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D127_HardworkingMan';
    $this->name = ('Hardworking Man');
    $this->deck = 'D';
    $this->number = 127;
    $this->category = FARM_PLANNER;
    $this->desc = [
      (
        'This card is an action space for you only. If each other player has more rooms than you, it provides the "Day Laborer", "Building Rooms", and "Major Improvement" actions (all three).'
      ),
    ];
    $this->players = '3+';
    $this->implemented = false;
  }
}
