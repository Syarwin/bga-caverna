<?php
namespace CAV\Actions;
use CAV\Managers\Meeples;
use CAV\Managers\Players;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Core\Stats;
use CAV\Helpers\Utils;

class Collect extends \CAV\Models\Action
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
    if (!empty($meeples)) {
      $eventData['meeples'] = $meeples;
      foreach ($meeples as $meeple) {
        $statName = 'incBoard' . ucfirst($meeple['type']);
        Stats::$statName($player);
      }
      $reorganize = $player->checkAutoReorganize($meeples);
      Notifications::collectResources($player, $meeples);
      Notifications::updateDropZones($player);

      $this->checkListeners('ImmediatelyAfterCollect', $player, $eventData);
      $player->checkAnimalsInReserve($reorganize);
      $this->checkListeners('AfterCollect', $player, $eventData);
    }
    $this->resolveAction();
  }

  public function isAutomatic($player = null)
  {
    return true;
  }
}
