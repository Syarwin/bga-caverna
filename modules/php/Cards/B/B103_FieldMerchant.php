<?php
namespace AGR\Cards\B;

class B103_FieldMerchant extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B103_FieldMerchant';
    $this->name = ('Field Merchant');
    $this->deck = 'B';
    $this->number = 103;
    $this->category = GOODS_PROVIDER;
    $this->desc = [
      (
        'When you play this card, you immediately get 1 wood and 1 reed. Each time you decline a "Minor/Major Improvement" action, you get 1 food/vegetable instead.'
      ),
    ];
    $this->players = '1+';
    $this->implemented = false;
  }
}
