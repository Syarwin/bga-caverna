<?php
namespace AGR\Cards\B;

class B124_Trimmer extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B124_Trimmer';
    $this->name = ('Trimmer');
    $this->deck = 'B';
    $this->number = 124;
    $this->category = BUILDING_RESOURCE_PROVIDER;
    $this->desc = [
      (
        'In each work phase, after you enclose at least one farmyard space, you get 2 stone. (Subdividing an existing pasture does not count.)'
      ),
    ];
    $this->players = '1+';
    $this->implemented = false;
  }
}
