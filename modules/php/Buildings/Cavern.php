<?php
namespace CAV\Buildings;

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

class Cavern extends \CAV\Models\Building
{
  public function __construct($row)
  {
    // throw new \feException(print_r($row));
    parent::__construct($row);
    $this->type = CAVERN;
    $this->name = clienttranslate('Cavern');
  }
}
