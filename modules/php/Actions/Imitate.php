<?php
namespace CAV\Actions;

use CAV\Managers\Meeples;
use CAV\Managers\Players;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Helpers\Utils;
use CAV\Core\Stats;

class Imitate extends \CAV\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = clienttranslate('Copy another used action space (except first player action)');
  }

  public function getState()
  {
    return ST_PLACE_TILE; // TODO
  }

  public function isDoable($player, $ignoreResources = false)
  {
    return false;
  }

  public function argsImitate()
  {
    $player = Players::getActive();

    return [];
  }

  public function actImitate($rooms)
  {
    self::checkAction('actImitate');
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
