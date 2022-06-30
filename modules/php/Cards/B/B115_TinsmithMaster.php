<?php
namespace AGR\Cards\B;

class B115_TinsmithMaster extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B115_TinsmithMaster';
    $this->name = ('Tinsmith Master');
    $this->deck = 'B';
    $this->number = 115;
    $this->category = CROP_PROVIDER;
    $this->desc = [
      (
        'You can hold 1 additional animal in each pasture without a stable. Each time you sow in a field, you can place 1 additional crop of the respective type in that field.'
      ),
    ];
    $this->players = '1+';
    $this->implemented = false;
  }
}
