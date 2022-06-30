<?php
namespace AGR\Cards\B;

class B86_TruffleSearcher extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B86_TruffleSearcher';
    $this->name = ('Truffle Searcher');
    $this->deck = 'B';
    $this->number = 86;
    $this->category = FARM_PLANNER;
    $this->desc = [
      ('This card can hold a number of wild boar equal to the number of completed feeding phases.'),
    ];
    $this->players = '1+';
    $this->implemented = false;
    $this->newSet = true;
  }
}
