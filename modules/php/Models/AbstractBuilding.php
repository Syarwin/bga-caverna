<?php
namespace CAV\Models;

use CAV\Managers\Meeples;
use CAV\Managers\Dwarves;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Helpers\Utils;
use CAV\Core\Globals;
use CAV\Core\Stats;
use CAV\Managers\Scores;
use CAV\Managers\ActionCards;
use CAV\Managers\Players;
use CAV\Managers\Buildings;

/*
 * Action cards for all
 */

class AbstractBuilding extends \CAV\Helpers\DB_Model
{
  protected $table = 'buildings';
  protected $primary = 'building_id';
  protected $attributes = [
    'id' => ['building_id', 'int'],
    'location' => 'building_location',
    'state' => ['building_state', 'int'],
    'extraDatas' => ['extra_datas', 'obj'],
    'pId' => ['player_id', 'int'],
    'x' => ['x', 'int'],
    'y' => ['y', 'int'],
  ];

  protected $id = null;
  protected $location = null;
  protected $state = null;
  protected $extraDatas = null;
  protected $pId = null;

  /*
   * STATIC INFORMATIONS
   *  they are overwritten by children
   */
  protected $staticAttributes = [
    'name',
    'tooltip',
    'text',
    'type',
    'vp',
    'field',
    'cost',
    'number',
    'nbInBox',
    'dwelling',
    'animalHolder',
  ];
  protected $name = '';
  protected $tooltip = [];
  protected $desc = []; // UI
  protected $text = []; // Text of the card, needed for front
  protected $stage = 0;
  protected $accumulation = []; // Array of resource => amount
  protected $type = null; // Class of the building
  protected $dwelling = 0;
  protected $container = 'central'; // UI
  protected $vp = 0;
  protected $field = false;
  protected $cost = [];
  protected $nbInBox = 1;
  protected $animalHolder = 0;
  // Constraints
  protected $players = null; // Players requirements => null if none, integer if only one, array otherwise

  public function jsonSerialize()
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'location' => $this->location,
      'state' => $this->state,
      'tooltip' => $this->tooltip,

      // 'component' => $this->isBoardComponent(),
      // 'desc' => $this->desc,
      'container' => $this->container,
    ];
  }

  public function isSupported($players, $options)
  {
    return $this->players == null || in_array(count($players), $this->players);
  }

  public function getActionCardType()
  {
    return $this->actionCardType ?? substr($this->id, 6);
  }

  public function getScore()
  {
    return $this->vp;
  }

  public function isPlayed()
  {
    return $this->location == 'inPlay';
  }

  public function getPlayer($checkPlayed = false)
  {
    if (!$this->isPlayed() && $checkPlayed) {
      throw new \feException("Trying to get the player for a non-played card : {$this->id}");
    }

    return Players::get($this->pId);
  }

  public function isDwelling()
  {
    return $this->dwelling > 0;
  }

  public function getDwellingCapacity()
  {
    return $this->dwelling;
  }

  public function isAnimalHolder()
  {
    return $this->animalHolder > 0;
  }

  public function getAnimalCapactiy()
  {
    return $this->animalHolder;
  }

  /**
   * Cost/buy function
   */
  public function getBaseCosts()
  {
    return $this->costs ?? [$this->cost];
  }

  public function getCosts($player, $args = [])
  {
    $costs = [];
    foreach ($this->getBaseCosts() as $cost) {
      $costs['trades'][] = array_merge(['max' => 1], $cost);
    }

    if (!is_null($this->fee)) {
      $costs['fee'] = $this->fee;
    }

    // Apply card effects
    $args['card'] = $this;
    $args['costs'] = $costs;
    Buildings::applyEffects($player, 'ComputeBuildingCosts', $args);
    return $args['costs'];
  }

  public function isBuyable($player, $ignoreResources = false, $args = [])
  {
    $cost = $this->getCosts($player, $args);
    return $ignoreResources || $player->canBuy($cost);
  }

  /**
   * Exchanges functions
   */
  public function getExchanges()
  {
    $exchanges = $this->exchanges;
    if (Globals::isHarvest()) {
      $flags = Globals::getExchangeFlags();
      Utils::filter($exchanges, function ($exchange) use ($flags) {
        return is_null($exchange['flag']) || !in_array($exchange['flag'], $flags);
      });
    }

    return $exchanges;
  }

  /**
   * Scores functions
   */
  protected function addScoringEntry($score, $desc, $isBonus = true, $player = null)
  {
    if (!$this->isPlayed()) {
      throw new \feException("Trying to addScoringEntry for a non-played card : {$this->id}");
    }

    if (is_string($desc)) {
      $desc = [
        'log' => $desc,
        'args' => [],
      ];
    }
    $player = $player ?? $this->getPlayer();
    $desc['args']['i18n'][] = 'card_name';
    $desc['args']['card_name'] = $this->getName();
    $desc['args']['player_name'] = $player ?? $player->getName();
    $desc['args']['score'] = $score;
    Scores::addEntry($player, $isBonus ? SCORING_CARDS_BONUS : SCORING_CARDS, $score, $desc, null, $this->getName());
  }

  protected function addBonusScoringEntry($score, $desc = null, $player = null)
  {
    $desc = $desc ?? $this->getBonusDescription();
    $this->addScoringEntry($score, $desc, true, $player);
  }

  protected function addQuantityScoringEntry($n, $scoresMap, $descSingular, $descPlural)
  {
    if (!$this->isPlayed()) {
      throw new \feException("Trying to addScoringEntry for a non-played card : {$this->id}");
    }

    Scores::addQuantityEntry(
      $this->getPlayer(),
      SCORING_CARDS_BONUS,
      $n,
      $scoresMap,
      $descSingular,
      $descPlural,
      $this->getName()
    );
  }

  public function computeScore()
  {
    if ($this->vp != 0) {
      $this->addScoringEntry(
        $this->vp,
        clienttranslate('${player_name} earns ${score} for owning ${card_name}'),
        false
      );
    }

    $this->computeBonusScore();
  }

  public function getBonusDescription()
  {
    return clienttranslate('${player_name} earns ${score} for bonus of ${card_name}');
  }

  public function computeBonusScore()
  {
    $bonus = $this->getBonusScore();
    if ($bonus != 0) {
      $this->addBonusScoringEntry($bonus);
    }
  }

  public function getBonusScore()
  {
    return $this->getExtraDatas(BONUS_VP) ?? 0;
  }

  public function incBonusScore($amount)
  {
    $newScore = $this->getBonusScore() + $amount;
    $this->setExtraDatas(BONUS_VP, $newScore);
    return $newScore;
  }

  public function endOfGameCleanup($resource, $map)
  {
    if ($this->isPlayed()) {
      $count = $this->getPlayer()->countReserveResource($resource);
      foreach ($map as $qty => $gain) {
        $lower = 0;
        $upper = null;

        // Quantity of the form : X-Y
        if (\stripos($qty, '-') !== false) {
          $t = \explode('-', $qty);
          $lower = (int) $t[0];
          $upper = (int) $t[1];
        }
        // Quantity of the form : +X
        elseif (\stripos($qty, '+') !== false) {
          $t = \explode('+', $qty);
          $lower = (int) $t[0];
        }
        // Quantity is just an int
        else {
          $lower = (int) $qty;
          $upper = (int) $qty;
        }

        // Check $n against $lower and $upper
        if ($count >= $lower && ($upper === null || $count <= $upper)) {
          $this->setExtraDatas(BONUS_VP, $gain);
          return $this->getPlayer()->useResource($resource, $lower);
        }
      }
    }
  }

  /**
   * Only keep exchanges corresponding to a trigger.
   * optional parameter $removeAnytime will allow to remove ANYTIME exchange if ask for a specific trigger
   *  -> useful to determine a card canCook and canBake booleans
   */
  public function getFilteredExchanges($trigger = ANYTIME, $removeAnytime = false)
  {
    $exchanges = $this->getExchanges();
    Utils::filterExchanges($exchanges, $trigger, $removeAnytime);
    return $exchanges;
  }

  public function canExchange($trigger = ANYTIME, $removeAnytime = false)
  {
    return count($this->getFilteredExchanges($trigger, $removeAnytime)) > 0;
  }

  // TODO: remove, no need to cook (I thnk)
  // public function canCook()
  // {
  //   return $this->canExchange(ANYTIME, true);
  // }
  //
  // public function canBake()
  // {
  //   return $this->canExchange(BREAD, true);
  // }

  public function actBuy($player, $args = [])
  {
    // Check $cost
    if (!$this->isBuyable($player, false, $args)) {
      throw new \BgaVisibleSystemException('Cannot be bought. Should not happen');
    }

    // Update stat
    // TODO
    //Stats::setCardPlayed($player->getId(), $this->getCode(), Globals::getTurn());

    // Update playerid if major
    $next = null;
    // if ($this->passing) {
    //   if (Globals::isSolo()) {
    //     self::DB()->update(['player_id' => null, 'card_location' => 'box'], $this->id);
    //   } else {
    //     $next = Players::get(Players::getNextId($player));
    //     self::DB()->update(['player_id' => $next->getId()], $this->id);
    //   }
    // } else {
    $maxPos = self::DB()
      ->where('building_location', 'inPlay')
      ->wherePlayer($player->getId())
      ->max('building_state');
    self::DB()->update(
      [
        'player_id' => $player->getId(),
        'building_location' => 'inPlay',
        'building_state' => $maxPos + 1,
      ],
      $this->id
    );
    $this->location = 'inPlay';
    // }
    $this->pId = $player->getId();

    // Notify
    Notifications::buyCard($this, $player);

    // Trigger of Pay if needed
    $cost = $this->getCosts($player, $args);
    if ($cost != NO_COST) {
      $player->pay(1, $cost, $this->name);
    }

    // additionnal effects of buying
    // TODO: check if needed
    // if ($this->isField()) {
    //   $player->board()->addFieldCard($this->id, $this->getFieldDetails());
    // }

    $flow = $this->onBuy($player);
    if (!is_null($flow)) {
      Engine::insertAsChild($flow);
    }

    $flow = $this->onBuyWithData($player, $args);
    if (!is_null($flow)) {
      Engine::insertAsChild($flow);
    }
  }

  // TODO: check if needed
  public function returnToBoard()
  {
    self::DB()->update(['player_id' => null, 'card_location' => 'board'], $this->id);
  }

  /**
   * Generic way to handle resources placed for future with cost
   */
  protected function getReceiveFlowWithCost($meeple, $cost, $source)
  {
    return [
      'type' => NODE_SEQ,
      'optional' => true,
      'childs' => [
        [
          'action' => PAY,
          'pId' => $this->pId,
          'args' => [
            'nb' => 1,
            'costs' => $cost,
            'source' => $source,
            'cardId' => $this->id,
          ],
        ],
        [
          'action' => RECEIVE,
          'args' => [
            'meeple' => $meeple['id'],
          ],
        ],
      ],
    ];
  }

  protected function onBuy($player)
  {
    return null;
  }

  protected function onBuyWithData($player, $eventData)
  {
    return null;
  }

  /**
   * Event modifiers template
   **/
  public function isListeningTo($event)
  {
    return false;
  }

  public function isAnytime($event, $action = null)
  {
    $node = Engine::getNextUnresolved();
    $ctxArgs = $node == null ? [] : $node->getArgs();
    return ($event['type'] ?? null) == 'anytime' &&
      $this->getPlayer()->getId() == Players::getActiveId() &&
      ($action == null || $event['action'] == $action) &&
      ($ctxArgs['buildingId'] ?? null) != $this->id;
  }

  protected function isActionCardEvent($event, $actionCardType, $playerConstraint = 'player', $immediately = false)
  {
    return $event['type'] == 'action' &&
      $event['action'] == 'PlaceDwarf' &&
      ($event['actionCardType'] ?? null) == $actionCardType &&
      (is_null($playerConstraint) ||
        ($playerConstraint == 'player' && $this->pId == $event['pId']) ||
        ($playerConstraint == 'opponent' && $this->pId != $event['pId'])) &&
      ((!$immediately && $event['method'] == 'PlaceDwarf') ||
        ($immediately && $event['method'] == 'ImmediatelyAfterPlaceDwarf'));
  }

  protected function isActionCardTurnEvent($event, $turns, $playerConstraint = 'player', $immediately = false)
  {
    $cardId = $event['actionCardId'] ?? null;
    if ($cardId != null) {
      $card = Utils::getActionCard($cardId);
      $turn = $card->getTurn();
      if (in_array($turn, $turns)) {
        $type = $card->getActionCardType();
        return $this->isActionCardEvent($event, $type);
      }
    }
  }

  protected function isActionEvent($event, $action, $playerConstraint = 'player', $immediately = false)
  {
    return $event['type'] == 'action' &&
      $event['action'] == $action &&
      (is_null($playerConstraint) ||
        ($playerConstraint == 'player' && $this->pId == $event['pId']) ||
        ($playerConstraint == 'opponent' && $this->pId != $event['pId'])) &&
      (($immediately && $event['method'] == 'ImmediatelyAfter' . $action) ||
        (!$immediately && $event['method'] == 'After' . $action));
  }

  protected function isBeforeEvent($event, $action)
  {
    return $event['type'] == 'action' &&
      $event['action'] == $action &&
      $this->pId == $event['pId'] &&
      $event['method'] == 'before' . $action;
  }

  protected function isDuringActionEvent($event, $action, $playerConstraint = 'player')
  {
    return $event['type'] == 'action' &&
      $event['action'] == $action &&
      (is_null($playerConstraint) ||
        ($playerConstraint == 'player' && $this->pId == $event['pId']) ||
        ($playerConstraint == 'opponent' && $this->pId != $event['pId'])) &&
      $event['method'] == $action;
  }

  protected function isPlayerEvent($event)
  {
    return $this->pId == $event['pId'];
  }

  protected function isBeforeCollectEvent($event, $res = null, $playerConstraint = 'player')
  {
    $cardType = $event['actionCardType'] ?? null;

    if (!$this->isActionCardEvent($event, $cardType, $playerConstraint)) {
      return false;
    }

    return $this->isCollectionSpace($event, $res);
  }

  protected function isCollectEvent($event, $res = null, $immediately = false, $playerConstraint = 'player')
  {
    if (!$this->isActionEvent($event, 'Collect', $playerConstraint, $immediately)) {
      return false;
    }

    return $this->isCollectionSpace($event, $res);
  }

  protected function isCollectionSpace($event, $res = null)
  {
    $actionCard = Utils::getActionCard($event['actionCardId']);
    if (!$actionCard->hasAccumulation()) {
      return false;
    }

    if ($res == null) {
      return true;
    }

    foreach ($actionCard->getAccumulation() as $resource => $amount) {
      if ($resource == $res) {
        return true;
      }
    }
    return false;
  }

  public function enforceReorganizeOnLastHarvest()
  {
    return false;
  }

  public function isActionCard()
  {
    return $this->actionCard;
  }

  public function getMaxBonus($bonusAttribute)
  {
    $player = $this->getPlayer();
    $playedBuildings = $player->getPlayedBuildings();
    $score = -1;
    $id = '';
    $f = 'is' . $bonusAttribute;

    foreach ($playedBuildings as $building) {
      if (!$building->$f()) {
        continue;
      }

      $tmp = $building->computeBonus();
      if ($tmp > $score) {
        $score = $tmp;
        $id = $building->getId();
      }
    }
    return $id;
  }

  public function isFlagged($data = 'used')
  {
    // check legacy values
    if ($data == 'used') {
      $used = $this->getExtraDatas('used') ?? 0;
      $done = $this->getExtraDatas('done') ?? 0;
      $flag = $this->getExtraDatas('flag') ?? 0;

      return $used || $done || $flag;
    }

    return $this->getExtraDatas($data) ?? 0;
  }

  /****************************
   ****** SYNTAXIC SUGAR *******
   ****************************/
  public function refreshDropZones()
  {
    Notifications::updateDropZones($this->getPlayer());
  }

  public function gainNode($gain, $pId = null)
  {
    $gain['pId'] = $pId ?? $this->pId;
    return [
      'action' => GAIN,
      'args' => $gain,
      'source' => $this->name,
      'cardId' => $this->getId(),
    ];
  }

  public function payNode($cost, $sourceName = null, $nb = 1, $to = null, $pId = null)
  {
    return [
      'action' => PAY,
      'args' => [
        'pId' => $pId ?? $this->pId,
        'nb' => $nb,
        'costs' => Utils::formatCost($cost),
        'source' => $sourceName ?? $this->name,
        'to' => $to,
      ],
    ];
  }

  public function payGainNode($cost, $gain, $sourceName = null, $optional = true, $pId = null)
  {
    $pId = $pId ?? $this->pId;

    return [
      'type' => NODE_SEQ,
      'optional' => $optional,
      'pId' => $pId,
      'childs' => [$this->payNode($cost, $sourceName), $this->gainNode($gain, $pId)],
    ];
  }

  public function receiveNode($mId, $updateObtained = false)
  {
    return [
      'action' => RECEIVE,
      'args' => [
        'meeple' => $mId,
        'updateObtained' => $updateObtained,
      ],
    ];
  }

  public function futureMeeplesNode(
    $resources,
    $turns,
    $flagCard = null,
    $cardId = null,
    $pId = null,
    $optional = false
  ) {
    // allow single integer argument for "next N rounds" cases
    if (!is_array($turns)) {
      $n = $turns;
      $turns = [];
      for ($i = 1; $i <= $n; $i++) {
        $turns[] = '+' . $i;
      }
    }

    return [
      'action' => PLACE_FUTURE_MEEPLES,
      'optional' => $optional,
      'args' => [
        'pId' => $pId ?? $this->pId,
        'resources' => $resources,
        'turns' => $turns,
        'flagCard' => $flagCard,
        'cardId' => $cardId,
      ],
    ];
  }

  // public function bakeBreadNode()
  // {
  //   return [
  //     'action' => EXCHANGE,
  //     'optional' => true,
  //     'pId' => $this->pId,
  //     'args' => [
  //       'trigger' => BREAD,
  //     ],
  //   ];
  // }

  public function useActionSpaceNode($cId, $farmer = null)
  {
    return [
      'type' => NODE_SEQ,
      'childs' => [
        [
          'action' => SPECIAL_EFFECT,
          'args' => [
            'cardId' => $this->id,
            'method' => 'moveMeepleToActionSpace',
            'args' => [$cId, $farmer],
          ],
        ],
        [
          'action' => SPECIAL_EFFECT,
          'args' => [
            'cardId' => $this->id,
            'method' => 'getActionSpaceBonusFlow',
            'args' => [$cId],
          ],
        ],
        [
          'action' => SPECIAL_EFFECT,
          'args' => [
            'cardId' => $this->id,
            'method' => 'useActionSpace',
            'args' => [$cId],
          ],
        ],
        [
          'action' => SPECIAL_EFFECT,
          'args' => [
            'cardId' => $this->id,
            'method' => 'getActionSpaceBonusFlow',
            'args' => [$cId, 'ImmediatelyAfter'],
          ],
        ],
        [
          'action' => SPECIAL_EFFECT,
          'args' => [
            'cardId' => $this->id,
            'method' => 'getActionSpaceBonusFlow',
            'args' => [$cId, 'After'],
          ],
        ],
      ],
    ];
  }

  public function moveMeepleToActionSpace($cId, $dwarf)
  {
    if (is_null($dwarf)) {
      return;
    }

    Meeples::moveToCoords($dwarf['id'], $cId);
    Notifications::placeFarmer($this->getPlayer(), $dwarf['id'], ActionCards::get($cId));
  }

  public function getUseActionSpaceDescription($cId)
  {
    return [
      'log' => clienttranslate('Use ${action_space}'),
      'args' => [
        'i18n' => ['action_space'],
        'action_space' => Utils::getActionCard($cId)->getName(),
      ],
    ];
  }

  public function useActionSpace($cId)
  {
    $flow = $this->getActionSpaceFlow($cId);

    if ($flow != []) {
      Engine::insertAsChild($flow);
    }
  }

  public function getActionSpaceFlow($cId)
  {
    $flow = [];

    $player = $this->getPlayer();
    $space = Utils::getActionCard($cId);

    if ($space->canBePlayed($player, 'dummy')) {
      $flow = $space->getFlow($player);
    }

    return $flow;
  }

  public function getActionSpaceBonusFlow($cId, $timing = '')
  {
    $type = Utils::getActionCard($cId)->getActionCardType();

    $event = [
      'pId' => $this->getPlayer()->getId(),
      'type' => 'action',
      'action' => 'PlaceDwarf',
      'method' => $timing . 'PlaceDwarf',
      'actionCardId' => $cId,
      'actionCardType' => $type,
    ];

    $reaction = PlayerCards::getReaction($event);
    if (!is_null($reaction)) {
      Engine::insertAsChild($reaction);
    }
  }

  public function flagCardNode($data = 'used')
  {
    return [
      'action' => SPECIAL_EFFECT,
      'args' => [
        'cardId' => $this->id,
        'method' => 'flagCard',
        'args' => [$data],
      ],
    ];
  }

  public function unflagCardNode($data = 'used')
  {
    return [
      'action' => SPECIAL_EFFECT,
      'args' => [
        'cardId' => $this->id,
        'method' => 'unflagCard',
        'args' => [$data],
      ],
    ];
  }

  public function isIndependentFlagCard()
  {
    return true;
  }

  public function flagCard($data = 'used')
  {
    $this->setExtraDatas($data, 1);
  }

  public function isIndependentUnflagCard()
  {
    return true;
  }

  public function unflagCard($data = 'used')
  {
    // unflag legacy values
    if ($data == 'used') {
      $this->setExtraDatas('done', 0);
      $this->setExtraDatas('flag', 0);
    }

    $this->setExtraDatas($data, 0);
  }

  public function getNextResource($type = null)
  {
    $meeples = Meeples::getResourcesOnCard($this->id, null, $type);
    return $meeples->empty() ? null : $meeples->last();
  }

  ///////////////////////////////////////////////////////////////////
  // public function getInitialLocation()
  // {
  //   return $this->stage == 0 ? 'board' : ['deck', $this->stage];
  // }

  // public function getTurn()
  // {
  //   $loc = $this->getLocation();
  //   $t = explode('_', $loc);
  //   if ($t[0] != 'turn') {
  //     return 0;
  //   }
  //
  //   return (int) $t[1];
  // }
  //
  // public function isBoardComponent()
  // {
  //   return $this->stage == 0;
  // }
  //
  // public function hasAccumulation()
  // {
  //   if (count($this->accumulation) == 0) {
  //     return false;
  //   }
  //   return true;
  // }
  //
  // public function getAccumulation()
  // {
  //   return $this->accumulation;
  // }
  //
  // public function accumulate()
  // {
  //   $ids = [];
  //   if ($this->hasAccumulation()) {
  //     foreach ($this->accumulation as $resource => $amount) {
  //       if (is_array($amount)) {
  //         $n = Meeples::getResourcesOnCard(self::getId())->count();
  //         $amount = $n == 0 ? $amount[0] : $amount[1];
  //       }
  //       $ids = array_merge($ids, Meeples::createResourceOnCard($resource, self::getId(), $amount));
  //     }
  //   }
  //   return $ids;
  // }
}
