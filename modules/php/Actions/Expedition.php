<?php
namespace CAV\Actions;

use CAV\Managers\Meeples;
use CAV\Managers\Players;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Helpers\Utils;
use CAV\Core\Stats;

class Expedition extends \CAV\Models\Action
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
        'args' => ['tiles' => [TILE_TUNNEL]],
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
        'args' => ['types' => ['D_Dwelling'], 'costs' => [STONE => 2, WOOD => 2, 'max' => 1]],
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
    $this->description = clienttranslate('Go to an expedition');
  }

  public function getState()
  {
    return ST_EXPEDITION;
  }

  public function isDoable($player, $ignoreResources = false)
  {
    return ($this->getDwarf()['weapon'] ?? 0) > 0;
  }

  public function argsExpedition()
  {
    $player = Players::getActive();
    $args = $this->getCtxArgs();

    return [
      'n' => $args['lvl'],
      'max' => $this->getDwarf()['weapon'] ?? 0,
      // TODO: add doable actions only
    ];
  }

  public function actExpedition($items)
  {
    self::checkAction('actExpedition');
    $args = $this->argsExpedition();

    if (count($items) > $this->argsExpedition()['n']) {
      throw new \BgaVisibleSystemException('Invalid loot selection');
    }
    if (empty($items)) {
      throw new \BgaUserException(clienttranslate('You must loot some items'));
    }

    $player = Players::getCurrent();
    $max = $args['max'];
    $childs = [];

    foreach ($items as $item) {
      if (!isset($this->loot[$item])) {
        throw new \BgaVisibleSystemException('Invalid loot during expedition. Should not happen');
      }
      if ($this->loot[$item]['lvl'] > $max) {
        throw new \BgaVisibleSystemException(
          'Invalid level of loot. Should not happen. Loot ' . $item . ' max ' . $max
        );
      }

      // insert node
      $childs[] = $this->loot[$item]['flow'];
    }

    // Upgrade the weapon of the Dwarf making the expedition
    $childs[] = [
      'action' => \WEAPON_INCREASE,
      'args' => ['dwarfs' => [$this->getDwarf()]],
    ];
    Engine::insertAsChild(['type' => NODE_SEQ, 'childs' => $childs]);

    // Listeners for cards
    $eventData = [
      'loot' => $items,
    ];
    $this->checkAfterListeners($player, $eventData);

    // upgrade dwarf weapon

    $this->resolveAction();
  }
}
