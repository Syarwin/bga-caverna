<?php
namespace AGR\Cards\B;

class B157_Salter extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B157_Salter';
    $this->name = ('Salter');
    $this->deck = 'B';
    $this->number = 157;
    $this->category = FOOD_PROVIDER;
    $this->desc = [
      (
        'At any time, you can pay 1 sheep/wild boar/cattle from you farm. If you do, place 1 food on each of the next 3/5/7 round spaces. At the start of these rounds, you get the food.'
      ),
    ];
    $this->players = '4+';
    $this->implemented = false;
  }
}
