<?php
namespace AGR\Cards\D;

class D113_FoodMerchant extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D113_FoodMerchant';
    $this->name = ('Food Merchant');
    $this->deck = 'D';
    $this->number = 113;
    $this->category = CROP_PROVIDER;
    $this->desc = [
      (
        'For each grain you harvest from a field, you can buy 1 vegetable for 3 food. If you harvest the last grain from a field, the vegatble costs you only 2 food.'
      ),
    ];
    $this->players = '1+';
    $this->implemented = false;
  }
}
