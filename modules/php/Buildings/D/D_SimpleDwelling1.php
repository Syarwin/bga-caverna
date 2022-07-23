<?php
namespace CAV\Buildings\D;

use CAV\Helpers\Utils;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Core\Globals;
use CAV\Core\Stats;
use CAV\Managers\Meeples;
use CAV\Managers\Scores;
use CAV\Managers\ActionCards;
use CAV\Managers\Players;
use CAV\Managers\Buildings;

class D_SimpleDwelling1 extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'D_SimpleDwelling1';
    $this->name = clienttranslate('Simple Dwelling');
    $this->dwelling = true;
    $this->nbInBox = 1;
    $this->desc = [clienttranslate('Room for exactly 1 Dwarf')];
    $this->costs = [[WOOD => 4], [STONE => 2]];
  }
}
