<?php
namespace CAV\Actions;

use CAV\Managers\Meeples;
use CAV\Managers\Players;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Helpers\Utils;

class Stables extends \CAV\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = clienttranslate('Build stables');
  }

  public function getState()
  {
    return ST_STABLE;
  }

  function getCosts($player)
  {
    $costs = $this->getCtxArgs()['costs'];
    $this->checkCostModifiers($costs, $player);
    return $costs;
  }

  public function isDoable($player, $ignoreResources = false)
  {
    // The player must be able to buy at least one stable and have an empty spot
    return ($ignoreResources || $player->canBuy($this->getCosts($player))) &&
      $player->countStablesInReserve() > 0 &&
      !empty($player->board()->getFreeZones(false));
  }

  public function getMaxBuildableStables($player)
  {
    $available = $player->countStablesInReserve();
    $maxBuyable = $player->maxBuyableAmount($this->getCosts($player));
    if (isset($this->getCtxArgs()['max'])) {
      $maxBuyable = $this->getCtxArgs()['max'];
    }
    return min($available, $maxBuyable);
  }

  public function argsStables()
  {
    $player = Players::getActive();
    $inReserve = $player->countStablesInReserve();
    $max = $this->getMaxBuildableStables($player);
    return [
      'zones' => $player->board()->getStableZone(),
      'max' => $max,
      'descSuffix' => $max == $inReserve ? 'nomore' : '',
    ];
  }

  public function actStables($stables)
  {
    // Sanity checks
    self::checkAction('actStables');
    $player = Players::getActive();
    if ($player->countStablesInReserve() < count($stables)) {
      throw new \BgaUserException(
        sprintf(
          clienttranslate('You do not have enough stables in your reserve. Remaining stables: %s'),
          $player->countStablesInReserve()
        )
      );
    }

    // Should not happen => ie guy is cheating
    $args = $this->argsStables();
    if ($args['max'] < count($stables)) {
      throw new \feException('Too many stables to create');
    }

    // Add them to board
    foreach ($stables as &$stable) {
      if (!in_array($stable, $args['zones'])) {
        throw new \feException('Invalid zone. Should not happen');
      }
      $player->board()->addStable($stable);
    }

    // Notify
    Notifications::stables($player, $stables);

    // Proceed
    $this->checkAfterListeners($player, ['stables' => $stables]);
    $player->pay(count($stables), $this->getCosts($player), clienttranslate('Stables'));
    $this->resolveAction(['stables' => $stables]);
  }
}
