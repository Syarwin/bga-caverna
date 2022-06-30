<?php
namespace AGR\Actions;
use AGR\Managers\Meeples;
use AGR\Managers\Players;
use AGR\Core\Notifications;
use AGR\Core\Engine;
use AGR\Core\Stats;
use AGR\Helpers\Utils;

class Collect extends \AGR\Models\Action
{
  public function getState()
  {
    return ST_COLLECT;
  }

  public function getDescription($ignoreResources = false)
  {
    $cardId = $this->ctx->getCardId();
    $player = Players::getActive();
    $meeples = Meeples::collectResourcesOnCard($player, $cardId);
    $res = Utils::reduceResources($meeples);
    return [
      'log' => clienttranslate('Collect ${resources_desc}'),
      'args' => [
        'resources_desc' => Utils::resourcesToStr($res),
      ],
    ];
  }

  public function stCollect()
  {
    $cardId = $this->ctx->getCardId();
    $player = Players::getActive();

    $eventData = [
      'actionCardId' => $cardId,
    ];

    $this->checkListeners('Collect', $player, $eventData);

    $meeples = Meeples::collectResourcesOnCard($player, $cardId);
    $eventData['meeples'] = $meeples;

    foreach ($meeples as $meeple) {
      $statName = 'incBoard' . ucfirst($meeple['type']);
      Stats::$statName($player);
    }
    $reorganize = $player->checkAutoReorganize($meeples);
    Notifications::collectResources($player, $meeples);
    Notifications::updateDropZones($player);
    $player->updateObtainedResources($meeples);

    $this->checkListeners('ImmediatelyAfterCollect', $player, $eventData);
    $player->checkAnimalsInReserve($reorganize);
    $this->checkListeners('AfterCollect', $player, $eventData);
    $this->resolveAction();
  }

  public function isAutomatic($player = null)
  {
    return true;
  }
}
