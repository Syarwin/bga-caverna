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
    return true;
  }

  public function isDoable($player, $ignoreResources = false)
  {
    if (!$player->hasFarmerInReserve()) {
      return false;
    }

    // Check constraints associated with the action
    $constraints = $this->ctx->getArgs()['constraints'] ?? [];
    foreach ($constraints as $c) {
      if (
        $player->hasPlayedCard('B10_Caravan') &&
        $c == 'freeRoom' &&
        $player->countDwarves() >= $player->countRooms() + 1
      ) {
        return false;
      } elseif (
        !$player->hasPlayedCard('B10_Caravan') &&
        $c == 'freeRoom' &&
        $player->countDwarves() >= $player->countRooms()
      ) {
        return false;
      }

      // Action space on additional board with an action blocked until turn5
      if ($c == 'turn5' && Globals::getTurn() < 5) {
        return false;
      }
    }

    return true;
  }

  public function stWishChildren()
  {
    $args = $this->ctx->getArgs();  
      
    $type = $args['type'] ?? null;
    $player = Players::getActive();
    if ($args['insideHouse'] ?? false) {
      $room = $player->getFreeRoom(); // Get a free room
      $meep = $player->growFamily([$room['x'], $room['y']], 'board');
    } elseif ($args['cardLocation'] ?? false) {
      $meep = $player->growFamily($args['cardLocation']);
    } else {
      $cardId = $this->ctx->getCardId(); // CardId is tagged in the flow tree associated to the action
      $meep = $player->growFamily($cardId);
    }

    Notifications::growFamily($player, $meep);
    Notifications::updateHarvestCosts();
    
    // Listeners for cards
    $eventData = [
      'farmers' => $player->countDwarves(),
    ];
    $this->checkAfterListeners($player, $eventData);

    $this->resolveAction();
    Engine::proceed();
  }
}
