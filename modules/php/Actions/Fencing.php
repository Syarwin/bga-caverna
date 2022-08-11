<?php
namespace CAV\Actions;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Managers\Meeples;
use CAV\Managers\Players;
use CAV\Helpers\Utils;
use CAV\Helpers\UserException;


class Fencing extends \CAV\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = clienttranslate('Build fences');
  }

  public function isDoable($player, $ignoreResources = false)
  {
    return false;
    return $player->board()->canCreateNewPasture();
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
