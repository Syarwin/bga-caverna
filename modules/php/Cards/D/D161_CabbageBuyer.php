<?php
namespace AGR\Cards\D;

class D161_CabbageBuyer extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D161_CabbageBuyer';
    $this->name = ('Cabbage Buyer');
    $this->deck = 'D';
    $this->number = 161;
    $this->category = CROP_PROVIDER;
    $this->desc = [
      (
        'Each time any player (including you) renovates and the builds no/ 1 minor/ 1 major improvement, you can buy 1 vegetable for 3/2/1 food.'
      ),
    ];
    $this->players = '4+';
    $this->implemented = false;
  }
}
