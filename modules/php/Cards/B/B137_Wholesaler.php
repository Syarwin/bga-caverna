<?php
namespace AGR\Cards\B;

class B137_Wholesaler extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B137_Wholesaler';
    $this->name = ('Wholesaler');
    $this->deck = 'B';
    $this->number = 137;
    $this->category = GOODS_PROVIDER;
    $this->desc = [
      (
        'Place 1 vegetable, 1 wild boar, 1 stone, and 1 cattle on this card. Each time you use an action space card on round spaces 8 to 11, you get the corresponding good from this card.'
      ),
    ];
    $this->players = '3+';
    $this->implemented = false;
    $this->newSet = true;
  }
}
