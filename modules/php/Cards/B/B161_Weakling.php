<?php
namespace AGR\Cards\B;

class B161_Weakling extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B161_Weakling';
    $this->name = ('Weakling');
    $this->deck = 'B';
    $this->number = 161;
    $this->category = CROP_PROVIDER;
    $this->desc = [
      (
        'Each time it is your turn in the work phase, if there are one or more accumulation spaces with 5+ goods on them and you do not use any of them, you get 1 vegetable.'
      ),
    ];
    $this->players = '4+';
    $this->implemented = false;
  }
}
