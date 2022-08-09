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

class D_StartDwelling extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'D_StartDwelling';
    $this->name = clienttranslate('Entry level Dwelling');
    $this->dwelling = 2;
    $this->nbInBox = 0;
    $this->animalHolder = 2;
    $this->desc = [clienttranslate('Room for exactly 2 Dwarves and 1 pair of anima')];
  }

  public function isSupported($players, $options)
  {
    return false; // Make sure StartDwelling are not created on building boards
  }
}
