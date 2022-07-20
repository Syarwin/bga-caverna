<?php
namespace CAV\Actions;

use CAV\Managers\Meeples;
use CAV\Managers\Players;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Helpers\Utils;
use CAV\Core\Stats;

class Expedition extends \CAV\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = clienttranslate('Go to an expedition');
  }

  public function getState()
  {
    return ST_EXPEDITION;
  }

  public function isDoable($player, $ignoreResources = false)
  {
    return true;
  }

  public function argsExpedition()
  {
    $player = Players::getActive();
    $args = $this->getCtxArgs();

    return [
      'n' => $args['lvl'],
      'max' => $this->getDwarf()['weapon'],
    ];
  }

  public function actExpedition($items)
  {
    self::checkAction('actExpedition');
    if (count($items) > $this->argsExpedition()['max']) {
      throw new \BgaVisibleSystemException('Invalid loot selection');
    }

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
