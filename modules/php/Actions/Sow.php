<?php
namespace AGR\Actions;
use AGR\Managers\Players;
use AGR\Managers\Meeples;
use AGR\Core\Notifications;
use AGR\Core\Engine;

class Sow extends \AGR\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = clienttranslate('Sow');
  }

  public function getState()
  {
    return ST_SOW;
  }

  public function isDoable($player, $ignoreResources = false)
  {
    $reserve = $player->getAllReserveResources();
    return $player->board()->canSow($reserve, $ignoreResources);
  }

  function argsSow()
  {
    $player = Players::getActive();
    $reserve = $player->getAllReserveResources();
    return [
      VEGETABLE => $reserve[VEGETABLE],
      GRAIN => $reserve[GRAIN],
      WOOD => $reserve[WOOD],
      'zones' => $player->board()->getSowableFields($reserve),
      'max' => $this->maxZones(),
    ];
  }

  function maxZones()
  {
    return $this->getCtxArgs()['max'] ?? 99;
  }

  function actSow($crops)
  {
    self::checkAction('actSow');

    // Sanity checks
    $args = $this->argsSow();
    $fields = [];
    foreach ($args['zones'] as $field) {
      $fields[$field['uid'] ?? $field['id']] = $field;
    }
    $choices = [GRAIN => 0, VEGETABLE => 0, WOOD => 0];
    foreach ($crops as $crop) {
      if (!array_key_exists($crop['id'], $fields)) {
        throw new \BgaVisibleSystemException('You can\'t sow a crop here');
      }
      $choices[$crop['crop']]++;
    }

    if ($choices[GRAIN] + $choices[VEGETABLE] + $choices[WOOD] == 0) {
      throw new \BgaVisibleSystemException('You must sow at least one crop');
    }
    if ($choices[GRAIN] > $args[GRAIN]) {
      throw new \BgaVisibleSystemException('You can\'t sow that much grain');
    }
    if ($choices[VEGETABLE] > $args[VEGETABLE]) {
      throw new \BgaVisibleSystemException('You can\'t sow that many vegetables');
    }
    if ($choices[WOOD] > $args[WOOD]) {
      throw new \BgaVisibleSystemException('You can\'t sow that much wood');
    }

    if ($choices[GRAIN] + $choices[VEGETABLE] + ($choices[WOOD] > 0) > $this->maxZones()) {
      throw new \BgaVisibleSystemException("You can't sow this many fields");
    }

    // Add them to board (update $pos variable to add info about the meeple)
    $player = Players::getActive();
    $sows = [];
    foreach ($crops as $crop) {
      $field = $fields[$crop['id']];

      if (isset($field['constraints'])) {
        if ($crop['crop'] != $field['constraints']) {
          throw new \BgaVisibleSystemException('You must respect the contraints of the card');
        }
      }

      // Move existing crop
      $seed = $player->getNextCropToSow($crop['crop']);
      $location = $field['type'] == 'fieldCard' ? $field['id'] : 'board';
      Meeples::moveToCoords($seed['id'], $location, $field);
      $seed = Meeples::get($seed['id']);

      // Sow new crops
      $nbrs = [GRAIN => 2, VEGETABLE => 1, WOOD => 2];
      $ids = Meeples::createResourceInLocation(
        $crop['crop'],
        $location,
        $field['pId'],
        $field['x'],
        $field['y'],
        $nbrs[$crop['crop']]
      );

      $sows[] = [
        'field' => $field,
        'seed' => $seed,
        'crops' => Meeples::getMany($ids)->toArray(),
      ];
    }

    // Notify
    Notifications::sow($player, $sows);
    Notifications::updateDropZones($player);
    $player->forceReorganizeIfNeeded();

    $this->checkAfterListeners($player, ['sows' => $sows]);
    $this->resolveAction(['sows' => $sows]);
  }
}
