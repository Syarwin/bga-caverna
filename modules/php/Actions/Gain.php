<?php
namespace CAV\Actions;
use CAV\Managers\Meeples;
use CAV\Managers\Players;
use CAV\Managers\Buildings;
use CAV\Core\Notifications;
use CAV\Core\Stats;
use CAV\Helpers\Utils;

class Gain extends \CAV\Models\Action
{
  public function getState()
  {
    return ST_GAIN;
  }

  public function isIndependent($player = null)
  {
    $args = $this->getCtxArgs();
    foreach ($args as $resource => $amount) {
      if (\in_array($resource, [SHEEP, PIG, CATTLE])) {
        return false;
      }
    }

    if ($this->ctx->forceConfirmation()) {
      return false;
    }

    return true;
  }

  public function getPlayer()
  {
    $args = $this->getCtxArgs();
    $pId = $args['pId'] ?? Players::getActiveId();
    return Players::get($pId);
  }

  public function stGain()
  {
    $player = $this->getPlayer();
    $args = $this->getCtxArgs();
    $cardId = $this->ctx->getCardId();
    $source = $this->ctx->getSource();

    // Create resources
    $meeples = [];
    foreach ($args as $resource => $amount) {
      if ($amount == 0) {
        continue;
      }
      if (in_array($resource, ['cardId', 'skipReorganize', 'pId'])) {
        continue;
      }

      // SCORE won't create real meeples but still need to be here in the log and with animation
      if ($resource == SCORE) {
        $card = Buildings::get($cardId);
        $card->incBonusScore($amount);
        for ($i = 0; $i < $amount; $i++) {
          $meeples[] = ['type' => SCORE, 'location' => $cardId, 'pId' => $player->getId(), 'destroy' => true];
        }
        continue;
      }

      $meeples = array_merge($meeples, $player->createResourceInReserve($resource, $amount));

      // TODO
      // $statName = 'inc' . ($source == null ? 'Board' : 'Cards') . ucfirst($resource);
      // Stats::$statName($player, $amount);

      // if ($resource == GRAIN && $player->hasPlayedCard('C86_LivestockFeeder')) {
      //   Notifications::updateDropZones($player);
      // }
    }

    // Handle empty gain
    if (empty($meeples)) {
      $this->resolveAction();
      return;
    }

    $eventData = [
      'actionCardId' => $cardId,
      'meeples' => $meeples,
    ];

    // Auto reorganize if needed (return true if need to enter the state to confirm)
    $reorganize = $player->checkAutoReorganize($meeples);
    // Notify
    Notifications::gainResources($player, $meeples, $cardId, $source);
    if (!($args['skipReorganize'] ?? false)) {
      $player->checkAnimalsInReserve($reorganize);
    }
    $this->checkAfterListeners($player, $eventData);
    $this->resolveAction();
  }

  public function getDescription($ignoreResources = false)
  {
    return [
      'log' => clienttranslate('Gain ${resources_desc}'),
      'args' => [
        'resources_desc' => Utils::resourcesToStr($this->ctx->getArgs()),
      ],
    ];
  }

  public function isAutomatic($player = null)
  {
    return true;
  }
}
