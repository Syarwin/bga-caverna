<?php
namespace CAV\Core;
use CAV\Managers\Players;
use CAV\Helpers\Utils;
use CAV\Core\Globals;

class Notifications
{
  /*************************
   **** GENERIC METHODS ****
   *************************/
  protected static function notifyAll($name, $msg, $data)
  {
    self::updateArgs($data);
    Game::get()->notifyAllPlayers($name, $msg, $data);
  }

  protected static function notify($player, $name, $msg, $data)
  {
    $pId = is_int($player) ? $player : $player->getId();
    self::updateArgs($data);
    Game::get()->notifyPlayer($pId, $name, $msg, $data);
  }

  public static function message($txt, $args = [])
  {
    self::notifyAll('message', $txt, $args);
  }

  public static function messageTo($player, $txt, $args = [])
  {
    $pId = is_int($player) ? $player : $player->getId();
    self::notify($pId, 'message', $txt, $args);
  }

  public static function seed($seed)
  {
    self::notifyAll(
      'seed',
      clienttranslate('Want to play with same configuration ? Here is the seed of your game ${seed}'),
      [
        'seed' => $seed,
      ]
    );
  }

  public static function clearTurn($player, $notifIds)
  {
    self::notifyAll('clearTurn', clienttranslate('${player_name} restart their turn'), [
      'player' => $player,
      'notifIds' => $notifIds,
    ]);
  }

  // Remove extra information from cards
  protected function filterBuildingdDatas($building)
  {
    return [
      'id' => $building['id'],
      'location' => $building['location'],
      'pId' => $building['pId'],
      'x' => $building['x'],
      'y' => $building['y'],
    ];
  }
  public static function refreshUI($datas)
  {
    // Keep only the thing that matters
    $fDatas = [
      'meeples' => $datas['meeples'],
      'players' => $datas['players'],
      'scores' => $datas['scores'],
      'buildings' => $datas['buildings'],
      'tiles' => $datas['tiles'],
    ];

    foreach ($fDatas['buildings'] as $i => $building) {
      $fDatas['buildings'][$i] = self::filterBuildingdDatas($building);
    }

    self::notifyAll('refreshUI', '', [
      'datas' => $fDatas,
    ]);
  }

  public static function revealHarvestToken($token)
  {
    if ($token['type'] == HARVEST_NORMAL) {
      $msg = clienttranslate('A normal harvest will take place at the end of the turn');
    } elseif ($token['type'] == HARVEST_NONE) {
      $msg = clienttranslate('No harvest will take place this turn');
    } elseif ($token['type'] == \HARVEST_1FOOD) {
      $msg = clienttranslate('Pay 1 <FOOD> by dwarf instead of harvest this turn');
    } elseif ($token['type'] == \HARVEST_CHOICE) {
      $msg = clienttranslate('Only Field phase or Breed phase will be done this turn');
    }
    self::notifyAll('revealHarvestToken', $msg, ['token' => $token]);
  }

  public static function startHarvest($token)
  {
    self::notifyAll('startHarvest', $token['type'] == HARVEST_NONE ? '' : clienttranslate('Start of harvest phase'), [
      'token' => $token,
    ]);
  }

  public static function endHarvest($token)
  {
    self::notifyAll('endHarvest', '', [
      'token' => $token,
    ]);
  }

  /////////////////////////////////////////////////////////
  //  _   _            _               _            _
  // | | | |_ __   ___| |__   ___  ___| | _____  __| |
  // | | | | '_ \ / __| '_ \ / _ \/ __| |/ / _ \/ _` |
  // | |_| | | | | (__| | | |  __/ (__|   <  __/ (_| |
  //  \___/|_| |_|\___|_| |_|\___|\___|_|\_\___|\__,_|
  //
  /////////////////////////////////////////////////////////

  public static function startNewRound($round)
  {
    self::notifyAll('startNewRound', clienttranslate('Starting round n°${round}'), [
      'round' => $round,
    ]);
  }

  public static function revealActionCard($card)
  {
    self::notifyAll('revealActionCard', clienttranslate('Action __${action}__ is revealed'), [
      'action' => $card->getName(),
      'i18n' => ['action'],
      'card' => $card->getUiData(),
      'turn' => Globals::getTurn(),
    ]);
  }

  public static function flipWishChildren($oldCard, $newCard)
  {
    self::notifyAll('flipWishChildren', clienttranslate('Card ${oldName} is replaced by ${newName}'), [
      'oldName' => $oldCard->getName(),
      'newName' => $newCard->getName(),
      'i18n' => ['oldName', 'newName'],
      'card' => $newCard->getUiData(),
      'oldId' => $oldCard->getId(),
    ]);
  }

  public static function accumulate($meeples, $silent = false)
  {
    self::notifyAll('accumulation', $silent ? '' : clienttranslate('Accumulation spaces are being filled in'), [
      'resources' => $meeples->toArray(),
    ]);
  }

  public static function placeDwarf($player, $dwarfId, $card, $source = null)
  {
    if ($source != null) {
      $msg = clienttranslate('${player_name} places a person on card ${card_name} (${source})');
    } else {
      $msg = clienttranslate('${player_name} places a person on card ${card_name}');
    }
    self::notifyAll('placeDwarf', $msg, [
      'card' => $card,
      'player' => $player,
      'dwarf' => $dwarfId,
      'source' => $source,
    ]);
  }

  public static function imitate($player, $card)
  {
    self::notifyAll('message', clienttranslate('${player_name} copies ${card_name}'), [
      'card' => $card,
      'player' => $player,
    ]);
  }

  public static function equipWeapon($player, $dwarf, $weapon)
  {
    self::notifyAll('equipWeapon', clienttranslate('${player_name} forges a weapon of strength ${strength}'), [
      'player' => $player,
      'dwarf' => $dwarf['id'],
      'weapon' => $weapon,
      'strength' => $weapon['state'],
    ]);
  }

  public static function upgradeWeapon($player, $dwarfes, $source)
  {
    if ($source != null) {
      $msg = clienttranslate('${player_name} upgrades dwarf weapon (${source})');
    } else {
      $msg = clienttranslate('${player_name} upgrades dwarf weapon');
    }
    self::notifyAll('upgradeWeapon', $msg, [
      'player' => $player,
      'dwarfs' => $dwarfes->toArray(),
      'source' => $source,
    ]);
  }

  public static function growFamily($player, $meeple)
  {
    self::notifyAll('growFamily', clienttranslate('${player_name} family grows'), [
      'player' => $player,
      'meeple' => $meeple,
    ]);
  }

  public static function growChildren($children)
  {
    self::notifyAll('growChildren', clienttranslate('${nb} child(ren) become(s) adult(s)'), [
      'nb' => count($children),
      'ids' => $children,
    ]);
  }

  public static function constructFences($player, $fences)
  {
    $msg =
      count($fences) == 1
        ? clienttranslate('${player_name} constructs one fence')
        : clienttranslate('${player_name} constructs ${nb} fences');

    self::notifyAll('addFences', $msg, [
      'nb' => count($fences),
      'fences' => $fences,
      'player' => $player,
    ]);

    // Update drop zones of player
    self::updateDropZones($player);
  }

  public static function furnish($player, $building)
  {
    self::notifyAll('furnish', clienttranslate('${player_name} furnishes its cavern with ${building_name}'), [
      'player' => $player,
      'building' => $building,
    ]);
  }

  public static function plow($player, $field, $source = null)
  {
    if ($source != null) {
      $msg = clienttranslate('${player_name} plows one field (${source})');
    } else {
      $msg = clienttranslate('${player_name} plows one field');
    }
    self::notifyAll('plow', $msg, [
      'field' => $field,
      'player' => $player,
      'source' => $source,
    ]);
  }

  public static function sow($player, $sows, $noSeed = false)
  {
    $seeds = array_map(function ($sow) {
      return $sow['seed'];
    }, $sows);
    // throw new \feException(print_r($sows));
    if ($noSeed) {
      // we are sowing only one thing
      $seeds = [];
      foreach ($sows as $sow) {
        // throw new \feException(print_r($sow));
        $seeds[] = ['type' => array_shift($sow['crops'])['type']];
      }
    }
    // throw new \feException(print_r($seeds));

    self::notifyAll('sow', clienttranslate('${player_name} sows ${resources_desc}'), [
      'player' => $player,
      'resources' => $seeds,
      'sows' => $sows,
    ]);
  }

  public static function placeTile($player, $tile, $squares)
  {
    self::notifyAll('placeTile', clienttranslate('${player_name} places ${tile_name}'), [
      'i18n' => ['tile_name'],
      'tile_name' => \CAV\Actions\PlaceTile::getTileName($tile),
      'player' => $player,
      'squares' => $squares,
    ]);

    // TODO ?? Update drop zones of player
    // self::updateDropZones($player);
  }

  public static function stables($player, $stables)
  {
    $msg =
      count($stables) == 1
        ? clienttranslate('${player_name} constructs one stable')
        : clienttranslate('${player_name} constructs ${nb} stables');

    self::notifyAll('addStables', $msg, [
      'nb' => count($stables),
      'player' => $player,
      'stables' => $stables,
    ]);

    // Update drop zones of player
    self::updateDropZones($player);
  }

  public static function collectResources($player, $meeples)
  {
    self::notifyAll('collectResources', clienttranslate('${player_name} collects ${resources_desc}'), [
      'player' => $player,
      'resources' => $meeples,
    ]);
  }

  public static function moveAnimalsAround($player, $meeples)
  {
    self::notifyAll('collectResources', '', [
      'player' => $player,
      'resources' => $meeples,
    ]);
  }

  // Receive is for future resources on action card
  public static function receiveResource($player, $meeple)
  {
    self::notifyAll('collectResources', clienttranslate('${player_name} receives ${resources_desc}'), [
      'player' => $player,
      'resources' => [$meeple],
    ]);
  }

  public static function gainDwarf($player, $meeples, $source)
  {
    self::notifyAll('gainResources', clienttranslate('${player_name} gains a new dwarf'), [
      'i18n' => ['source'],
      'player' => $player,
      'resources' => $meeples,
      'source' => $source,
    ]);
  }

  public static function gainResources($player, $meeples, $cardId = null, $source = null)
  {
    if ($source != null) {
      $msg = clienttranslate('${player_name} gains ${resources_desc} (${source})');
    } else {
      $msg = clienttranslate('${player_name} gains ${resources_desc}');
    }

    self::notifyAll('gainResources', $msg, [
      'i18n' => ['source'],
      'player' => $player,
      'resources' => $meeples,
      //'cardId' => $cardId,    //doesn't seem to be necessary, but crashes if not null
      'source' => $source,
    ]);
  }

  public static function breed($player, $meeples, $source)
  {
    if ($source != null) {
      $msg = clienttranslate('${player_name} breeds ${resources_desc} (${source})');
    } else {
      $msg = clienttranslate('${player_name} breeds ${resources_desc}');
    }
    self::notifyAll('gainResources', $msg, [
      'i18n' => ['source'],
      'player' => $player,
      'resources' => $meeples,
      'source' => $source,
    ]);
  }

  public static function payResources($player, $resources, $source, $cardSources = [], $cardNames = [])
  {
    $data = [
      'i18n' => ['source'],
      'player' => $player,
      'resources' => $resources,
      'source' => $source,
    ];
    $msg = clienttranslate('${player_name} pays ${resources_desc} for ${source}');

    // Card sources modifiers
    if (!empty($cardSources)) {
      $msg = clienttranslate('${player_name} pays ${resources_desc} for ${source} (${cards})');
      $data['i18n'][] = 'cards';

      $log = [];
      $args = [];
      foreach ($cardSources as $i => $cardId) {
        $log[] = '${card' . $i . '}';
        $args['card' . $i] = $cardNames[$cardId];
        $args['i18n'][] = 'card' . $i;
      }
      $data['cards'] = [
        'log' => implode(', ', $log),
        'args' => $args,
      ];
    }

    self::notifyAll('payResources', $msg, $data);
  }

  public static function payResourcesTo($player, $resources, $source, $cardSources = [], $cardNames = [], $otherPlayer)
  {
    $data = [
      'i18n' => ['source'],
      'player' => $player,
      'resources' => $resources,
      'source' => $source,
      'player2' => $otherPlayer,
    ];
    $msg = clienttranslate('${player_name} pays ${resources_desc} to ${player_name2} for ${source}');

    self::notifyAll('collectResources', $msg, $data);
  }

  public static function payWithCard($player, $card, $source)
  {
    self::notifyAll('payWithCard', clienttranslate('${player_name} returns ${card_name} for ${source}'), [
      'i18n' => ['source'],
      'player' => $player,
      'card' => $card,
      'source' => $source,
    ]);
  }

  public static function discardAnimals($player, $resources)
  {
    self::notifyAll(
      'payResources',
      clienttranslate('${player_name} discards ${resources_desc} as no more room in the meadows'),
      [
        'player' => $player,
        'resources' => $resources,
      ]
    );
  }

  public static function endOfGame($player, $resources, $source)
  {
    $data = [
      'i18n' => ['source'],
      'player' => $player,
      'resources' => $resources,
      'source' => $source,
    ];

    self::notifyAll(
      'payResources',
      clienttranslate('${player_name} pays ${resources_desc} for bonus of ${source}'),
      $data
    );
  }

  /**
   * Silent kill slide the resources to top bar and destroy them whereas silent destroy simply destroy them
   */
  public static function silentKill($resources)
  {
    self::notifyAll('silentKill', '', [
      'resources' => $resources,
    ]);
  }

  public static function clearActionSpaces($resources)
  {
    self::notifyAll(
      'silentKill',
      clienttranslate('Clearing action spaces with more than 6 goods (not preserved by usage of ruby)'),
      [
        'resources' => $resources,
      ]
    );
  }

  public static function silentDestroy($resources)
  {
    self::notifyAll('silentDestroy', '', [
      'resources' => $resources,
    ]);
  }

  public static function destroyWeapon($player, $resources)
  {
    self::notifyAll('silentKill', clienttranslate('${player_name} converts weapon into food'), [
      'player' => $player,
      'resources' => $resources->toArray(),
    ]);
  }

  public static function returnHome($meeples)
  {
    self::notifyAll('returnHome', clienttranslate('End of turn. All dwarfs come back home'), [
      'dwarfs' => $meeples->toArray(),
    ]);
  }

  public static function adoptiveChildren($meeples)
  {
    self::notifyAll('returnHome', '', [
      'dwarfs' => $meeples->toArray(),
    ]);
  }

  public static function firstPlayer($player, $tokenId)
  {
    self::notifyAll('firstPlayer', clienttranslate('${player_name} takes the First player token'), [
      'player' => $player,
      'meepleId' => $tokenId,
    ]);
  }

  public static function updateDropZones($player)
  {
    self::notifyAll('updateDropZones', '', [
      'player' => $player,
      'zones' => $player->board()->getAnimalsDropZones(),
    ]);
  }

  public static function reorganize($player, $meeples)
  {
    self::notifyAll('reorganize', clienttranslate('${player_name} reorganizes their animals'), [
      'player' => $player,
      'meeples' => $meeples->toArray(),
    ]);
  }

  public static function harvestCrop($player, $crops)
  {
    self::notifyAll('harvestCrop', clienttranslate('${player_name} harvest ${resources_desc}'), [
      'player' => $player,
      'resources' => $crops,
    ]);
  }

  public static function placeMeeplesForFuture($player, $resources, $turns, $meeples)
  {
    $msg =
      count($turns) == 1
        ? clienttranslate('${player_name} puts ${resources_desc} on the action card of turn n°${turns}')
        : clienttranslate('${player_name} puts ${resources_desc} on the action cards of turns n°${turns}');

    self::notifyAll('placeMeeplesForFuture', $msg, [
      'player' => $player,
      'resources_desc' => Utils::resourcesToStr($resources),
      'turns' => implode(', ', $turns),
      'meeples' => $meeples->toArray(),
    ]);
  }

  public static function exchange($player, $deleted, $created, $source)
  {
    $msg =
      $source == ''
        ? clienttranslate('${player_name} converts ${resources_desc} into ${resources2_desc}')
        : clienttranslate('${player_name} converts ${resources_desc} into ${resources2_desc} (${source})');

    self::notifyAll('exchange', $msg, [
      'i18n' => ['source'],
      'player' => $player,
      'resources' => $deleted,
      'resources2' => $created,
      'source' => $source,
    ]);
  }

  public static function begging($player, $meeples)
  {
    self::notifyAll('gainResources', clienttranslate('${player_name} gets ${resources_desc} as food is missing'), [
      'player' => $player,
      'resources' => $meeples,
    ]);
  }

  public static function updateScores($scores)
  {
    self::notifyAll('updateScores', '', [
      'scores' => $scores,
    ]);
  }

  public static function updateHarvestCosts()
  {
    $data = [];
    foreach (Players::getAll() as $pId => $player) {
      $data[$pId] = $player->getHarvestFoodCost();
    }

    self::notifyAll('updateHarvestCosts', '', [
      'costs' => $data,
    ]);
  }

  public static function addCardToDraftSelection($player, $card, $pos)
  {
    self::notify($player, 'addCardToDraftSelection', '', [
      'cardId' => $card->getId(),
      'pos' => $pos,
    ]);
  }
  public static function removeCardFromDraftSelection($player, $card)
  {
    self::notify($player, 'removeCardFromDraftSelection', '', [
      'cardId' => $card->getId(),
    ]);
  }
  public static function confirmDraftSelection($card)
  {
    $pId = $card->getPId();
    $player = Players::get($pId);
    self::notify($pId, 'confirmDraftSelection', clienttranslate('${player_name} picks ${card_name} (${card_type})'), [
      'player' => $player,
      'card' => $card,
    ]);
  }
  public static function clearDraftPools()
  {
    self::notifyAll('clearDraftPools', '', []);
  }
  public static function draftIsOver()
  {
    self::notifyAll('draftIsOver', clienttranslate('Draft is over, starting the game now'), []);
  }

  public static function A71_ClearingSpade($player, $crops)
  {
    self::notifyAll(
      'harvestCrop',
      clienttranslate('${player_name} moves ${resources_desc} on empty fields (Clearing Spade\'s effect)'),
      [
        'player' => $player,
        'resources' => $crops,
      ]
    );
  }

  public static function C51_FishingNet($meeples)
  {
    self::notifyAll(
      'accumulation',
      clienttranslate('${resources_desc} are put on Fishing space (Fishing net\'s effect)'),
      [
        'resources' => $meeples->toArray(),
      ]
    );
  }

  /*************************
   ****** CARDS NOTIFS ******
   **************************/

  /*********************
   **** UPDATE ARGS ****
   *********************/
  /*
   * Automatically adds some standard field about player and/or card
   */
  protected static function updateArgs(&$data)
  {
    if (isset($data['resource'])) {
      $names = [
        WOOD => clienttranslate('wood'),
        CLAY => clienttranslate('clay'),
        REED => clienttranslate('reed'),
        STONE => clienttranslate('stone'),
        GRAIN => clienttranslate('grain'),
        VEGETABLE => clienttranslate('vegetable'),
        SHEEP => clienttranslate('sheep'),
        PIG => clienttranslate('pig'),
        CATTLE => clienttranslate('cattle'),
        FOOD => clienttranslate('food'),
        'dwarf' => clienttranslate('dwarf'),
      ];

      $data['resource_name'] = $names[$data['resource']];
      $data['i18n'][] = 'resource_name';
    }

    if (isset($data['player'])) {
      $data['player_name'] = $data['player']->getName();
      $data['player_id'] = $data['player']->getId();
      unset($data['player']);
    }

    if (isset($data['player2'])) {
      $data['player_name2'] = $data['player2']->getName();
      $data['player_id2'] = $data['player2']->getId();
      unset($data['player2']);
    }

    if (isset($data['card']) && \is_object($data['card'])) {
      $data['i18n'][] = 'card_name';
      $data['card_name'] = $data['card']->getName();
    }

    if (isset($data['building'])) {
      $data['i18n'][] = 'building_name';
      $data['building_name'] = $data['building']->getName();
    }

    if (isset($data['resources'])) {
      // Get an associative array $resource => $amount
      $resources = Utils::reduceResources($data['resources']);
      $data['resources_desc'] = Utils::resourcesToStr($resources);
    }

    if (isset($data['resources2'])) {
      // Get an associative array $resource => $amount
      $resources2 = Utils::reduceResources($data['resources2']);
      $data['resources2_desc'] = Utils::resourcesToStr($resources2);
    }
  }

  /*********************
   **********************
   *********************/
  public static function updateCounters()
  {
    // TODO
  }
}

?>
