<?php
namespace AGR\Cards\B;

class B152_JuniorArtist extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B152_JuniorArtist';
    $this->name = ('Junior Artist');
    $this->deck = 'B';
    $this->number = 152;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      (
        'Each time after you use the "Day Laborer" action space, you can pay 1 food to use an unoccupied "Traveling Players" or "Lessons" action space with the same person.'
      ),
    ];
    $this->players = '4+';
    $this->implemented = false;
  }
}
