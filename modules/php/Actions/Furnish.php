<?php
namespace CAV\Actions;

use CAV\Managers\Meeples;
use CAV\Managers\Players;
use CAV\Managers\Buildings;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Helpers\Utils;
use CAV\Helpers\Collection;
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
    return ST_FURNISH;
  }

  public function getAvailableBuildings()
  {
    $buildings = new Collection();
    $types = $this->getCtxArgs()['types'] ?? [null];

    foreach ($types as $type) {
      $buildings = $buildings->merge(Buildings::getAvailables($type));
    }
    return $buildings;
  }

  public function getBuyableBuildings($player, $ignoreResources = false)
  {
    $args = [
      'actionCardId' => $this->ctx != null ? $this->ctx->getCardId() : null,
    ];

    $buy = $this->getAvailableBuildings()->filter(function ($imp) use ($player, $ignoreResources, $args) {
      return $imp->isBuyable($player, $ignoreResources, $args);
    });

    return $buy;
  }

  public function isDoable($player, $ignoreResources = false)
  {
    return true;
    return !$this->getBuyableBuildings($player, $ignoreResources)->empty();
  }

  public function argsFurnish()
  {
    $player = Players::getActive();

    return ['buildings' => $this->getBuyableBuildings($player)->getIds()];
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
