<?php
namespace CAV\Actions;

use CAV\Managers\Meeples;
use CAV\Managers\Players;
use CAV\Core\Notifications;
use CAV\Core\Globals;
use CAV\Core\Engine;
use CAV\Helpers\Utils;
use CAV\Core\Stats;

class HarvestChoice extends \CAV\Models\Action
{
  protected $loot = [
    'increaseStrength' => [
      'lvl' => 1,
      'flow' => [
        'action' => \WEAPON_INCREASE,
        'args' => ['source' => 'expedition'],
      ],
    ],
    'dog' => ['lvl' => 1, 'flow' => ['action' => GAIN, 'args' => [DOG => 1]]],
    'wood' => ['lvl' => 1, 'flow' => ['action' => GAIN, 'args' => [WOOD => 1]]],
    'sheep' => ['lvl' => 2, 'flow' => ['action' => GAIN, 'args' => [SHEEP => 1]]],
    'grain' => ['lvl' => 2, 'flow' => ['action' => GAIN, 'args' => [GRAIN => 1]]],
    'donkey' => ['lvl' => 3, 'flow' => ['action' => GAIN, 'args' => [DONKEY => 1]]],
    'stone' => ['lvl' => 3, 'flow' => ['action' => GAIN, 'args' => [STONE => 1]]],
    'vegetable' => ['lvl' => 4, 'flow' => ['action' => GAIN, 'args' => [\VEGETABLE => 1]]],
    'ore' => ['lvl' => 4, 'flow' => ['action' => GAIN, 'args' => [\ORE => 2]]],
    'pig' => ['lvl' => 5, 'flow' => ['action' => GAIN, 'args' => [\PIG => 1]]],
    'gold' => ['lvl' => 6, 'flow' => ['action' => GAIN, 'args' => [\GOLD => 2]]],
    'furnish' => ['lvl' => 7, 'flow' => ['action' => FURNISH]],
    'stable' => [
      'lvl' => 8,
      'flow' => [
        'action' => STABLES,
        'args' => [
          'costs' => [
            'trades' => [[STONE => 1, 'max' => 1]],
          ],
        ],
      ],
    ],
    'tunnel' => [
      'lvl' => 9,
      'flow' => [
        'action' => PLACE_TILE,
        'tiles' => [TILE_TUNNEL],
      ],
    ],
    'smallPasture' => [
      'lvl' => 9,
      'flow' => [
        'action' => PLACE_TILE,
        'args' => ['tiles' => [TILE_PASTURE], 'cost' => [WOOD => 1]],
      ],
    ],
    'cattle' => ['lvl' => 10, 'flow' => ['action' => GAIN, 'args' => [\CATTLE => 1]]],
    'largePasture' => [
      'lvl' => 10,
      'flow' => [
        'action' => PLACE_TILE,
        'args' => ['tiles' => [TILE_LARGE_PASTURE], 'cost' => [WOOD => 2]],
      ],
    ],
    'meadow' => [
      'lvl' => 10,
      'flow' => [
        'action' => PLACE_TILE,
        'args' => [
          'tiles' => [TILE_MEADOW],
        ],
      ],
    ],
    'dwelling' => [
      'lvl' => 11,
      'flow' => [
        'action' => FURNISH,
        'args' => ['types' => ['D_Dwelling'], 'costs' => [STONE => 2, WOOD => 2]],
      ],
    ],
    'field' => [
      'lvl' => 12,
      'flow' => [
        'action' => PLACE_TILE,
        'args' => [
          'tiles' => [TILE_FIELD],
        ],
      ],
    ],
    'sow' => [
      'lvl' => 12,
      'flow' => ['action' => SOW],
    ],
    'cavern' => [
      'lvl' => 14,
      'flow' => [
        'action' => PLACE_TILE,
        'args' => ['tiles' => [TILE_CAVERN]],
      ],
    ],
    'breed' => ['lvl' => 14, 'flow' => ['action' => BREED, 'args' => ['max' => 2]]],
  ];

  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = clienttranslate('Choice for special harvest');
  }

  public function getState()
  {
    return ST_HARVEST_CHOICE;
  }

  public function argsHarvestChoice()
  {
    $player = Players::getActive();

    return [
      'possibilities' => [REAP, BREED],
    ];
  }

  public function actHarvestChoice($choice)
  {
    self::checkAction('actHarvestChoice');
    $player = Players::getActive();
    if (!in_array($choice, $this->argsHarvestChoice()['possibilities'])) {
      throw new \BgaVisibleSystemException('Unknown choice. Should not happen');
    }
    $choices = Globals::getHarvestChoice();
    if (is_null($choice)) {
      $choice = [];
    }

    $choices[$player->getId()] = $choice;
    Globals::setHarvestChoice($choices);
    // TODO: manage translation
    Notifications::message(clienttranslate('${player_name} chooses to do ${phase} for this harvest'), [
      'player' => $player,
      'phase' => $choice,
    ]);
    $this->resolveAction();
  }
}
