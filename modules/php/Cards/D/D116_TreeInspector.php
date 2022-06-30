<?php
namespace AGR\Cards\D;

class D116_TreeInspector extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D116_TreeInspector';
    $this->name = ('Tree Inspector');
    $this->deck = 'D';
    $this->number = 116;
    $this->category = BUILDING_RESOURCE_PROVIDER;
    $this->desc = [
      (
        'This card is a "1 Wood" accumulation space for you only. Each time the newly revealed action space card is a "Quarry" accumulation space, you must discard all wood from this card.'
      ),
    ];
    $this->players = '1+';
    $this->implemented = false;
  }
}
