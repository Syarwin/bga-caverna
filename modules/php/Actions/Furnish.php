<?php
namespace CAV\Actions;

use CAV\Managers\Meeples;
use CAV\Managers\Players;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Helpers\Utils;
use CAV\Core\Stats;

class Furnish extends \CAV\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = clienttranslate('Furnish your Cavern)');
  }

  public function getState()
  {
    return ST_CONSTRUCT; // TODO
  }

  public function isDoable($player, $ignoreResources = false)
  {
    return false;
  }

  public function argsFurnish()
  {
    $player = Players::getActive();

    return [];
  }

  public function actFurnish($rooms)
  {
    self::checkAction('actFurnish');
    die('NOT DONE YET');

    $player = Players::getCurrent();

    // Listeners for cards
    $eventData = [
      'roomType' => $roomType,
      'rooms' => $rooms,
      'oldRoomCount' => $oldRoomCount,
    ];
    $this->checkAfterListeners($player, $eventData);

    $this->resolveAction();
  }
}
