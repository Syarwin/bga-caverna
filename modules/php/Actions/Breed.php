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

  public function getDescription($ignoreResources = false)
  {
    $args = $this->argsBreed();
    if (count($args['breeds']) > 0) {
      $res = [];
      foreach($args['breeds'] as $type){
        $res[$type] = 1;
      }

      return [
        'log' => clienttranslate('Breed ${resources_desc}'),
        'args' => [
          'resources_desc' => Utils::resourcesToStr($res),
        ],
      ];
    } else {
      return clienttranslate('Breed');
    }
  }


  public function getState()
  {
    return ST_BREED;
  }

  public function getBreedType($player)
  {
    $animals = $player->breedTypes();
    $args = $this->getCtxArgs()['animals'] ?? FARM_ANIMALS;
    $breed = [];

    foreach ($animals as $animal => $c) {
      if ($c === true && in_array($animal, $args)) {
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
    $types = $this->getBreedType($player);

    return [
      'breeds' => $types,
      'max' => $args['max'] ?? 4,
      'descSuffix' => ($args['max'] ?? 4) == 4 ? ($player->hasRuby()? 'ruby' : '') : 'choice',
    ];
  }

  public function isAutomatic($player = null)
  {
    $player = $player ?? Players::getActive();
    $args = $this->argsBreed();
    return count($args['breeds']) == 4 || (!$player->hasRuby() && count($args['breeds']) <= $args['max']);
  }

  public function stBreed()
  {
    $args = $this->argsBreed();
    $player = Players::getActive();
    if ($this->isAutomatic()) {
      $this->actBreed($args['breeds'], true);
    }
  }

  public function actBreed($breeds, $isAuto = false)
  {
    self::checkAction('actBreed', $isAuto);
    $player = Players::getCurrent();
    $args = $this->argsBreed();
    $breeds = array_unique($breeds);

    foreach ($breeds as $animal) {
      if (!in_array($animal, $args['breeds'])) {
        throw new \BgaVisibleSystemException('You cannot breed this animal. Should not happen');
      }
    }
    if (count($breeds) > ($args['max'] ?? 4)) {
      throw new \BgaVisibleSystemException('Too many animals to breed. Should not happen');
    }

    $reorganize = $player->breed(null, null, $breeds);
    if($reorganize){
      // Inserting leaf REORGANIZE
      Engine::insertAsChild([
        'pId' => $player->getId(),
        'action' => REORGANIZE,
        'args' => [
          'trigger' => $this->getCtxArgs()['trigger'] ?? BREED,
        ],
      ]);
      Engine::save();
    }

    $this->resolveAction();
  }
}
