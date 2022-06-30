<?php
namespace AGR\Cards\B;

class B94_StockProtector extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B94_StockProtector';
    $this->name = ('Stock Protector');
    $this->deck = 'B';
    $this->number = 94;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      (
        'Each time before you use the "Fencing" action space, you get 2 wood. Immediately after that "Fencing" action, you can place another person.'
      ),
    ];
    $this->players = '1+';
    $this->implemented = false;
  }
}
