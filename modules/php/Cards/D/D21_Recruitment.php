<?php
namespace AGR\Cards\D;

class D21_Recruitment extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D21_Recruitment';
    $this->name = ('Recruitment');
    $this->deck = 'D';
    $this->number = 21;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      (
        'Provided you have room in your house, each time you get a "Minor Improvement" action, you can take a "Family Growth" action instead.'
      ),
    ];
    $this->cost = [
      FOOD => '1',
    ];
    $this->prerequisite = ('No People Left in the House');
    $this->implemented = false;
  }
}
