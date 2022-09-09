<?php
namespace CAV\Buildings\Y;

use CAV\Managers\Buildings;

class Y_MainStorage extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_MainStorage';
    $this->category = 'bonus';
    $this->name = clienttranslate('Main Storage');
    $this->desc = [];
    $this->tooltip = [
      clienttranslate(
        'When scoring, the Main storage will be worth 2 Bonus points for each Furnishing tile with a yellow name tag, including the Main storage itself. __(All Parlors, Storages and Chambers have a yellow name tag.)__'
      ),
    ];
    $this->cost = [WOOD => 2, STONE => 1];
    $this->beginner = true;
  }

  public function computeBonusScore()
  {
    $c = 0;
    $buildings = Buildings::getOfPlayer($this->getPId());
    foreach ($buildings as $b => $building) {
      if (substr($building->getType(), 0, 2) == 'Y_') {
        $c++;
      }
    }
    $this->addBonusScoringEntry($c * 2);
  }
}
