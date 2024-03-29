<?php
namespace CAV\Actions;
use CAV\Managers\Meeples;
use CAV\Managers\Players;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Core\Stats;
use CAV\Helpers\Utils;

class Reap extends \CAV\Models\Action
{
  public function getState()
  {
    return ST_REAP;
  }

  public function isAutomatic($player = null)
  {
    return true;
  }

  public function getDescription($ignoreResources = false)
  {
    $player = Players::getActive();
    $crops = $player->board()->getHarvestCrops();
    $res = Utils::reduceResources($crops);
    return [
      'log' => clienttranslate('Reap ${resources_desc}'),
      'args' => [
        'resources_desc' => Utils::resourcesToStr($res),
      ],
    ];
  }

  public function stReap()
  {
    // Get growing crops
    $player = Players::getActive();
    $crops = $player->board()->getHarvestCrops();

    // Move them to player reserve
    foreach ($crops as &$crop) {
      Meeples::moveToCoords($crop['id'], 'reserve');
      $crop['originalLocation'] = $crop['location'];
      $crop['location'] = 'reserve';

      if (in_array($crop['type'], [\VEGETABLE, \GRAIN])) {
        $statName = 'incHarvested' . ucfirst($crop['type']);
        Stats::$statName($player);
      }
    }

    // Notify
    Notifications::harvestCrop($player, $crops);
    Notifications::updateDropZones($player);

    $eventData = [
      'crops' => $crops,
    ];
    $this->checkAfterListeners($player, $eventData);
    $this->resolveAction();
  }
}
