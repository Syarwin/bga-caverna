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
    'furnish' => ['lvl' => 7, 'flow' => ['action' => FURNISH]], // TODO check
    'stable' => ['lvl' => 8, 'flow' => ['action' => STABLES]],
    'tunnel' => ['lvl' => 9, 'flow' => ['action' => CONSTRUCT]], // TODO check
    'smallPasture' => [
      'lvl' => 9,
      'flow' => [
        'type' => NODE_SEQ,
        'childs' => [
          [
            'action' => PAY,
            'args' => [
              'nb' => 1,
              'costs' => [
                'trades' => [[WOOD => 1]],
              ],
            ],
          ],
          ['action' => \FENCING, 'args' => ['size' => 1]],
        ],
      ],
    ],
    'cattle' => ['lvl' => 10, 'flow' => ['action' => GAIN, 'args' => [\CATTLE => 1]]],
    'largePasture' => [
      'lvl' => 10,
      'flow' => [
        'type' => NODE_SEQ,
        'childs' => [
          [
            'action' => PAY,
            'args' => [
              'nb' => 1,
              'costs' => [
                'trades' => [[WOOD => 2]],
              ],
            ],
          ],

          ['action' => \FENCING, 'args' => ['size' => 2]],
        ],
      ],
    ],
    'meadow' => [
      'lvl' => 10,
      'flow' => [
        'action' => CONSTRUCT,
        'args' => [
          'tiles' => [TILE_MEADOW_FIELD], // TODO: prairie only
        ],
      ],
    ],
    'dwelling' => [
      'lvl' => 11,
      'flow' => [
        'action' => FURNISH,
        'args' => [], //TODO add args?
      ],
    ],
    'field' => [
      'lvl' => 12,
      'flow' => [
        'action' => CONSTRUCT,
        'args' => [
          'tiles' => [TILE_MEADOW_FIELD], // TODO : field only
        ],
      ],
    ],
    'sow' => ['lvl' => 12, ['action' => SOW, 'args' => [\VEGETABLE => 2, GRAIN => 2]]],
    'cavern' => ['lvl' => 14, ['action' => CONSTRUCT, 'args' => ['tiles' => []]]], // TODO: add cavern tile
    'breed' => ['lvl' => 14, ['special' => 'breed']],
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
    return true;
    return ($this->getDwarf()['weapon'] ?? 0) > 0;
  }

  public function argsExpedition()
  {
    $player = Players::getActive();
    $args = $this->getCtxArgs();

    return [
      'n' => $args['lvl'],
      'max' => $this->getDwarf()['weapon'],
    ];
  }

  public function actExpedition($items)
  {
    self::checkAction('actExpedition');
    $args = $this->argsExpedition();

    if (count($items) > $this->argsExpedition()['max']) {
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
      'args' => ['dwarves' => [$this->getDwarf()]],
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
