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
    $this->description = clienttranslate('Furnish your Cavern');
  }

  public function getState()
  {
    return ST_FURNISH;
  }

  // Return the list of available buildings (type might be enforced, eg : Dwelling)
  public function getAvailableBuildings()
  {
    $types = $this->getCtxArgs()['types'] ?? null;
    return Buildings::getAvailables($types);
  }

  // Return the list of building that can be purchased and have a valid placement zone
  public function getBuyableBuildings($player, $ignoreResources = false)
  {
    $args = [
      'actionCardId' => $this->ctx != null ? $this->ctx->getCardId() : null,
      'costs' => $this->getCtxArgs()['costs'] ?? null,
    ];

    $buildings = [];
    foreach ($this->getAvailableBuildings() as $building) {
      if (!$building->isBuyable($player, $ignoreResources, $args)) {
        continue;
      }

      // We have to loop because of that pair of building that replace each other...
      $zones = $player->board()->getBuildableZones($building);
      if (!empty($zones)) {
        $buildings[$building->getId()] = $zones;
      }
    }

    return $buildings;
  }

  public function isDoable($player, $ignoreResources = false)
  {
    return !empty($this->getBuyableBuildings($player, $ignoreResources));
  }

  public function argsFurnish()
  {
    $player = Players::getActive();
    return ['buildings' => $this->getBuyableBuildings($player)];
  }

  public function actFurnish($buildingId, $zone)
  {
    self::checkAction('actFurnish');

    $player = Players::getCurrent();
    $buildings = $this->getBuyableBuildings($player);
    if (!array_key_exists($buildingId, $buildings)) {
      throw new \feException('Building cannot be bought. Should not happen');
    }
    if (!in_array($zone, $buildings[$buildingId])) {
      throw new \feException('You can\'t put that building here. Should not happen');
    }

    // Replace cavern with the new building
    $building = $player->board()->addBuilding($buildingId, $zone);
    Notifications::furnish($player, $building);

    // Trigger effects
    $building->actFurnish($player, $zone['x'], $zone['y'], $this->getCtxArgs() ?? []);

    // Listeners for cards
    $eventData = [
      'buildingId' => $buildingId,
      'buildingType' => $building->getType(),
      'pos' => $zone,
    ];
    $this->checkAfterListeners($player, $eventData);

    $this->resolveAction();
  }
}
