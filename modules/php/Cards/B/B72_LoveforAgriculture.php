<?php
namespace AGR\Cards\B;

class B72_LoveforAgriculture extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B72_LoveforAgriculture';
    $this->name = ('Love for Agriculture');
    $this->deck = 'B';
    $this->number = 72;
    $this->category = CROP_PROVIDER;
    $this->desc = [
      (
        'You can sow crops in pastures covering 1 or 2 farmyard spaces. If you do, these pastures are also considered fields and hold 1 and 2 animals less, respectively.'
      ),
    ];
    $this->implemented = false;
  }
}
