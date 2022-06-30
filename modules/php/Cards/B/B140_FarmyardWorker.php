<?php
namespace AGR\Cards\B;

class B140_FarmyardWorker extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B140_FarmyardWorker';
    $this->name = ('Farmyard Worker');
    $this->deck = 'B';
    $this->number = 140;
    $this->category = FOOD_PROVIDER;
    $this->desc = [
      (
        'At the end of each work phase in which you placed at least 1 good on 1 of your farmyard spaces, you get 2 food.'
      ),
    ];
    $this->players = '3+';
    $this->implemented = false;
  }
}
