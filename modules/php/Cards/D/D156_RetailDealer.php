<?php
namespace AGR\Cards\D;

class D156_RetailDealer extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D156_RetailDealer';
    $this->name = ('Retail Dealer');
    $this->deck = 'D';
    $this->number = 156;
    $this->category = GOODS_PROVIDER;
    $this->desc = [
      (
        'Place 3 grain and 3 food on this card. Each time you use the "Resource Market" action space, you also get 1 grain and 1 food from this card.'
      ),
    ];
    $this->players = '4+';
    $this->implemented = false;
  }
}
