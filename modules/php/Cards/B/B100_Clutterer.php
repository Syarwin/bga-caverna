<?php
namespace AGR\Cards\B;

class B100_Clutterer extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B100_Clutterer';
    $this->name = ('Clutterer');
    $this->deck = 'B';
    $this->number = 100;
    $this->category = POINTS_PROVIDER;
    $this->desc = [
      (
        'During scoring, you get 1 bonus point for each card played after this on that has "accumulation space(s)" in its text.'
      ),
    ];
    $this->players = '1+';
    $this->implemented = false;
  }
}
