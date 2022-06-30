<?php
namespace AGR\Actions;
use AGR\Core\Notifications;
use AGR\Core\Engine;
use AGR\Managers\Fences;
use AGR\Managers\Meeples;
use AGR\Managers\Players;
use AGR\Helpers\Utils;
use AGR\Helpers\UserException;


class Fencing extends \AGR\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = clienttranslate('Build fences');
  }

  protected function isMiniPasture()
  {
    return $this->getCtxArgs()['miniPasture'] ?? false;
  }

  protected function isFieldFences()
  {
    return $this->getCtxArgs()['fieldFences'] ?? false;
  }

  public function isDoable($player, $ignoreResources = false)
  {
    $nFences = self::getMaxBuildableFences($player, $ignoreResources);
    return $player->board()->canCreateNewPasture($nFences);
  }

  public function getMaxBuildableFences($player, $ignoreResources = false)
  {
    $available = Fences::countAvailable($player->getId());
    $maxBuyable = $ignoreResources ? 15 : $player->maxBuyableAmount($this->getCosts($player));
    if ($this->isFieldFences() && !$ignoreResources) {
      $maxBuyable += count($player->board()->getAvailableFieldFences());
    }
    return min($available, $maxBuyable);
  }

  public function getState()
  {
    return ST_FENCING;
  }

  function argsFencing()
  {
    $player = Players::getActive();
    $inReserve = Fences::countAvailable($player->getId());
    $max = self::getMaxBuildableFences($player);
    return [
      'costs' => self::getCosts($player),
      'max' => $max,
      'zones' => $player->board()->getFencableZones(),
      'miniPasture' => $this->isMiniPasture(),
      'fieldFences' => $this->isFieldFences(),
      'descSuffix' => $this->isMiniPasture()
        ? 'minipasture'
        : ($this->isFieldFences()
          ? 'fieldfences'
          : ($max == $inReserve
            ? 'nomore'
            : '')),
    ];
  }

  function getCosts($player)
  {
    $costs = $this->getCtxArgs()['costs'];
    $this->checkCostModifiers($costs, $player);
    return $costs;
  }

  /**
   * Create fences
   * @param $fences associative array x,y
   **/
  function actFencing($fences)
  {
    self::checkAction('actFence');
    $player = Players::getActive();
    $fieldFences = $player->board()->getAvailableFieldFences();

    // Sanity checks on number of fences
    if (count($fences) > $this->getMaxBuildableFences($player)) {
      throw new \BgaVisibleSystemException('You can\'t build that many fences with your resources');
    }

    // Add them to board
    $playerBoard = $player->board();
    $oldPastures = $playerBoard->getPastures();
    foreach ($fences as &$fence) {
      $playerBoard->addFence($fence);
    }

    // Then run sanity checks with $raiseException = true to auto rollback in case of invalid choice
    $playerBoard->areFencesValid(true);
    $playerBoard->arePasturesValid(true);

    $pastures = $playerBoard->getPastures(true);
    $newPastures = [
      // Useful for ShepherdsCrook
      'all' => Utils::diffPastures($pastures, $oldPastures, false),
      'new' => Utils::diffPastures($pastures, $oldPastures, true),
    ];

    if ($this->isMiniPasture()) {
      // throw new \feException(print_r($newPastures));
      $miniPastureError = totranslate('You must fence exactly one farmyard space');
      if (count($newPastures['new']) != 1 || count($newPastures['all']) != 1) {
        throw new UserException($miniPastureError);
      }

      foreach ($newPastures['new'] as $pasture) {
        if (isset($pasture['nodes']) && count($pasture['nodes']) != 1) {
          throw new \BgaUserException($miniPastureError);
        }
      }
    }

    if (!$player->board()->areAnimalsValid(false)) {
      Engine::insertAsChild([
        'action' => REORGANIZE,
      ]);
    }

    $countFieldFences = 0;
    if ($this->isFieldFences()) {
      foreach ($fences as $id => $fen) {
        if (in_array(['x' => $fen['x'], 'y' => $fen['y']], $fieldFences)) {
          $countFieldFences++;
        }
      }
      if (!$player->canBuy($this->getCosts($player), count($fences) - $countFieldFences)) {
        throw new \BgaUserException(
          totranslate('You must build fences adjacent to fields if you want to build that many fences')
        );
      }
    }

    // Notify
    Notifications::constructFences($player, $fences);
    $player->forceReorganizeIfNeeded();
    $this->checkAfterListeners($player, [
      'fences' => $fences,
      'newPastures' => $newPastures,
    ]);

    // Proceed
    if (!$this->isMiniPasture()) {
      $player->pay(count($fences) - $countFieldFences, $this->getCosts($player), clienttranslate('Fencing'));
    }
    $this->resolveAction(['fences' => $fences]);
  }
}
