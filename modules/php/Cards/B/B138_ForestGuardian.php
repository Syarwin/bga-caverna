<?php
namespace AGR\Cards\B;

class B138_ForestGuardian extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B138_ForestGuardian';
    $this->name = ('Forest Guardian');
    $this->deck = 'B';
    $this->number = 138;
    $this->category = GOODS_PROVIDER;
    $this->desc = [
      (
        'When you play this card, you immediately get 2 wood. Each time before another player takes at least 5 wood from an accumulation space, they must first pay you 1 food.'
      ),
    ];
    $this->players = '3+';
    $this->implemented = false;
  }
}
