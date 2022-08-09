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
    $this->category = 'dwelling';
    $this->name = clienttranslate('Simple Dwelling');
    $this->desc = [clienttranslate('Room for exactly 1 Dwarf')];
    $this->tooltip = [
      clienttranslate(
        'The Simple dwellings are cheaper than the ordinary Dwellings by 1 building material (here: 1 Stone) but therefore they are not worth any Gold points.'
      ),
      clienttranslate('They provide room for exactly 1 Dwarf'),
    ];
    $this->dwelling = 1;
    $this->costs = [[WOOD => 4], [STONE => 2]];
  }
}
