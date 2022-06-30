<?php
namespace AGR\Cards\B;

class B65_GrainDepot extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B65_GrainDepot';
    $this->name = ('Grain Depot');
    $this->deck = 'B';
    $this->number = 65;
    $this->category = CROP_PROVIDER;
    $this->desc = [
      (
        'If you paid wood/clay/stone for this card, place 1 grain on each of the next 2/3/4 round spaces. At the start of these rounds, you get the grain.'
      ),
    ];
    $this->costText = ('2 Wood/2 Clay/2 Stone');
    $this->implemented = false;
  }
}
