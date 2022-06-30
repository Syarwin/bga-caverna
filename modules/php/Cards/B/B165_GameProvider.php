<?php
namespace AGR\Cards\B;

class B165_GameProvider extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B165_GameProvider';
    $this->name = ('Game Provider');
    $this->deck = 'B';
    $this->number = 165;
    $this->category = LIVESTOCK_PROVIDER;
    $this->desc = [
      (
        'Immediately before each harvest, you can discard 1/3/4 grain from different fields to get 1/2/3 wild boars.'
      ),
    ];
    $this->players = '4+';
    $this->implemented = false;
  }
}
