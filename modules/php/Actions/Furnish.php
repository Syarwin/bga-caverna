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
    return !$this->getBuyableBuildings($player, $ignoreResources)->empty();
  }

  public function argsFurnish($full = false)
  {
    $player = Players::getActive();

    if (!$full) {
      $buildings = $this->getBuyableBuildings($player)->getIds();
    } else {
      $buildings = $this->getBuyableBuildings($player);
    }
    return ['buildings' => $buildings];
  }

  public function actFurnish($tile)
  {
    self::checkAction('actFurnish');

    // $tile = ['id' , 'x', 'y'];
    $args = $this->argsFurnish(true);
    $player = Players::getCurrent();

    // checking card is buyable
    if (!in_array($tile['id'], $args['buildings']->getIds())) {
      throw new \feException('Tile cannot be bought. Should not happen');
    }

    $playerBoard = $player->board();
    $building = $args['buildings'][$tile['id']];

    // check on mountain (not sure it's necessary)
    if (!$playerBoard->isMoutainZone($tile)) {
      throw new \feException('Cannot furnish outside of the mountain');
    }
    // checking than we have a cavern
    $cavern = array_filter($playerBoard->getBuildings(), function ($c) use ($tile) {
      return $c->getType() == CAVERN && $tile['x'] == $c->getX() && $tile['y'] == $c->getY();
    });

    if (empty($cavern)) {
      throw new \feException('No cavern on this spot. Cannot furnish. Should not happen');
    }
    $cavernId = $cavern[0]->getId();
    // throw new \feException(Buildings::get($tile['id'])->getId());
    // Replace cavern with the new building
    Buildings::addBuilding($tile, $cavernId, $player);
    Notifications::furnish($player, Buildings::get($tile['id']), $cavernId);

    // Trigger of Pay if needed
    $cost = $building->getCosts($player, $this->getCtxArgs());
    if ($cost != NO_COST) {
      $player->pay(1, $cost, $building->getName());
    }
    $playerBoard->refresh();

    // Listeners for cards
    $eventData = [
      'building' => $tile,
    ];
    $this->checkAfterListeners($player, $eventData);

    $this->resolveAction();
  }
}
