<?php
namespace CAV\Actions;

use CAV\Managers\Players;
use CAV\Managers\Buildings;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Helpers\Utils;
use CAV\Core\Stats;

class Breed extends \CAV\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
  }

  public function getState()
  {
    return ST_BREED;
  }

  public function getBreedType($player)
  {
    $animals = $player->countAnimalsOnBoard();
    $args = $this->getCtxArgs()['animals'] ?? ANIMALS;
    $breed = [];

    foreach ($animals as $animal => $c) {
      if ($animal != DOG && $c >= 2 && in_array($animal, $args)) {
        $breed[] = $animal;
      }
    }

    return $breed;
  }

  public function isDoable($player, $ignoreResources = false)
  {
    return count($this->getBreedType($player)) > 0;
  }

  public function argsBreed()
  {
    $player = Players::getActive();
    $args = $this->getCtxArgs();

    return [
      'breeds' => $this->getBreedType($player),
      'max' => $args['max'] ?? 99,
      'descSuffix' => ($args['max'] ?? 99) == 99 ? '' : 'choice',
    ];
  }

  public function actBreed($breeds)
  {
    self::checkAction('actBreed');

    $player = Players::getCurrent();
    $args = $this->argsBreed();
    $breeds = array_unique($breeds);

    foreach ($breeds as $animal) {
      if (!in_array($animal, $args['breeds'])) {
        throw new \BgaVisibleSystemException('You cannot breed this animal. Should not happen');
      }
    }
    if (count($breeds) > ($args['max'] ?? 99)) {
      throw new \BgaVisibleSystemException('Too many animals to breed. Should not happen');
    }

    $created = $player->breed(null, null, $breeds);

    // Inserting leaf REORGANIZE
    Engine::insertAsChild(
      [
        'pId' => $player->getId(),
        'action' => REORGANIZE,
        'args' => [
          'trigger' => HARVEST,
        ],
      ],
      ['order' => 'harvestBreed']
    );
    Engine::save();

    $this->resolveAction();
  }
}
