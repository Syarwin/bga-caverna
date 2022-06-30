<?php
namespace AGR\Cards\B;

class B167_StableSergeant extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B167_StableSergeant';
    $this->name = ('Stable Sergeant');
    $this->deck = 'B';
    $this->number = 167;
    $this->category = LIVESTOCK_PROVIDER;
    $this->desc = [
      (
        'When you play this card, you can pay 2 food to get 1 sheep, 1 wild boar, and 1 cattle, but only if you can accommodate all three animals on your farm.'
      ),
    ];
    $this->players = '4+';
    $this->implemented = false;
  }
}
