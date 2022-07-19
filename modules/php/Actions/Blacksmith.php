<?php
namespace CAV\Actions;

use CAV\Managers\Meeples;
use CAV\Managers\Players;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Helpers\Utils;
use CAV\Core\Stats;

class Blacksmith extends \CAV\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = clienttranslate('Forge a Weapon');
  }

  public function getState()
  {
    return ST_BLACKSMITH;
  }

  public function isDoable($player, $ignoreResources = false)
  {
    // TODO handle BlackSmith building
    return $ignoreResources || $player->getReserveResource(ORE)->count() > 0;
  }

  public function argsBlacksmith()
  {
    $player = Players::getActive();

    return [];
  }

  public function actBlacksmith($rooms)
  {
    self::checkAction('actBlacksmith');
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
