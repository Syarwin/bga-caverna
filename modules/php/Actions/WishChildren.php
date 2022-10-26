<?php
namespace CAV\Actions;

use CAV\Core\Globals;
use CAV\Managers\Players;
use CAV\Core\Notifications;
use CAV\Core\Engine;

class WishChildren extends \CAV\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = clienttranslate('Grow family');
  }

  public function getState()
  {
    return ST_WISHCHILDREN;
  }

  public function isAutomatic($player = null)
  {
    return !$this->isOptional();
  }

  public function isDoable($player, $ignoreResources = false)
  {
    if (!$player->hasDwarfInReserve()) {
      return false;
    }

    // Check constraints associated with the action
    $constraints = $this->ctx->getArgs()['constraints'] ?? [];
    foreach ($constraints as $c) {
      if ($c == 'freeRoom' && $player->countDwarfs() >= $player->countDwellings()) {
        return false;
      }
    }

    return true;
  }

  public function isOptional()
  {
    return $this->ctx->getInfos()['optional'] ?? false;
  }

  public function argsWishChildren()
  {
    return [];
  }

  public function stWishChildren()
  {
    if ($this->isAutomatic()) {
      $this->actWishChildren(true);
    }
  }

  public function actWishChildren($auto = false)
  {
    $this->checkAction('actWishChildren', $auto);
    $args = $this->getCtxArgs();

    $type = $args['type'] ?? null;
    $player = Players::getActive();
    $cardId = $this->ctx->getCardId(); // CardId is tagged in the flow tree associated to the action
    $meep = $player->growFamily($cardId);

    Notifications::growFamily($player, $meep);
    Notifications::updateHarvestCosts();
    if ($player->countDwarfs() == 5) {
      Notifications::updateDwellingCapacity($player);
    }

    // Listeners for cards
    $eventData = [
      'farmers' => $player->countDwarfs(),
    ];
    $this->checkAfterListeners($player, $eventData);

    $this->resolveAction();
    Engine::proceed();
  }
}
