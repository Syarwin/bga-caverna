<?php
namespace AGR\Cards\D;

class D167_PureBreeder extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D167_PureBreeder';
    $this->name = ('Pure Breeder');
    $this->deck = 'D';
    $this->number = 167;
    $this->category = LIVESTOCK_PROVIDER;
    $this->desc = [
      (
        'You immediately get 1 wood. After each round that does not end with a harvest, you can breed exactly one type of animal. (This is not considered a breeding phase.)'
      ),
    ];
    $this->players = '4+';
    $this->implemented = false;
  }
}
