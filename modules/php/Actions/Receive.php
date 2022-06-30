<?php
namespace AGR\Actions;
use AGR\Managers\Meeples;
use AGR\Managers\Players;
use AGR\Core\Notifications;
use AGR\Core\Engine;
use AGR\Core\Stats;
use AGR\Helpers\Utils;

// Receive goods that were put on an action card for future round
// or move an existing meeple off a card or space and into the reserve
class Receive extends \AGR\Models\Action
{
  public function getState()
  {
    return ST_RECEIVE;
  }

  public function getDescription($ignoreResources = false)
  {
    $meeple = $this->getMeeple();
    return [
      'log' => clienttranslate('Receive ${resources_desc}'),
      'args' => [
        'resources_desc' => Utils::resourcesToStr([$meeple['type'] => 1]),
      ],
    ];
  }

  public function getMeeple()
  {
    $mId = $this->getCtxArgs()['meeple'];
    return Meeples::get($mId);
  }

  public function isAutomatic($player = null)
  {
    return true;
  }

  public function isIndependent($player = null)
  {
    $meeple = $this->getMeeple();
    return !in_array($meeple['type'], [SHEEP, PIG, CATTLE, FIELD]);
  }

  public function stReceive()
  {
    // Receive the meeple
    $meeple = $this->getMeeple();
    $player = Players::getActive();
    Meeples::receiveResource($player, $meeple);
    if (!in_array($meeple['type'], ['field','roomStone'])) {
      $statName = 'incCards' . ucfirst($meeple['type']);
      Stats::$statName($player);
    }

    $meeples = [$meeple];
    $reorganize = $player->checkAutoReorganize($meeples);
    
    $eventData = [
      'meeples' => $meeples,
    ];    

    // Notify
    Notifications::receiveResource($player, $meeple);

    if ($this->getCtxArgs()['updateObtained'] ?? false) {
      $player->updateObtainedResources([$meeple]);
    }

    // Add special action for field
    if ($meeple['type'] == 'field') {
      if ($player->board()->canPlow()) {
        Engine::insertAsChild([
          'action' => PLOW,
          'optional' => true,
          'pId' => $player->getId(),
        ]);
      } else {
        Notifications::message(clienttranslate('${player_name} can`t plow the received field'), [
          'player_name' => $player->getName(),
        ]);
      }
    }
	
    // Add special action for B14 (stone room)
    if ($meeple['type'] == 'roomStone') {
      if ($player->board()->canConstruct() && $player->getRoomType() == 'roomStone') {
        Engine::insertAsChild([
          'action' => CONSTRUCT,
          'optional' => true,
          'pId' => $player->getId(),
          'args' => ['costs' => Utils::formatCost(['max' => 1]), 'max' => 1],		  
        ]);
      } else {
        Notifications::message(clienttranslate('${player_name} can`t build the received room'), [
          'player_name' => $player->getName(),
        ]);
      }
    }	
    Notifications::updateDropZones($player);
    // Check animals in reserve
    $player->checkAnimalsInReserve($reorganize);
    $this->checkAfterListeners($player, $eventData);
    $this->resolveAction();
  }
}
