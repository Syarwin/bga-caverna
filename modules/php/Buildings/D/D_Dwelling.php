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

/*
 * PlayerCard: parent of Major/minor improvements and Occupation
 */

class D_DWelling extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'D_DWelling';
    $this->name = clienttranslate('Dwelling');
    $this->dwelling = true;
    $this->nbInBox = 99;
    $this->desc = [clienttranslate('Room for exactly 1 Dwarf')];
    $this->costs = [[WOOD => 4], [STONE => 3]];
    $this->vp = 3;
  }
}
