<?php
namespace CAV\Models;
use CAV\Helpers\Utils;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Core\Globals;
use CAV\Core\Stats;
use CAV\Managers\Meeples;
use CAV\Managers\Scores;
use CAV\Managers\ActionCards;
use CAV\Managers\Players;
use CAV\Managers\Buildings;

/*
 * PlayerCard: parent of Major/minor improvements and Occupation
 */

class PlayerCard extends AbstractCard
{
  protected $implemented = true; // For DEV only
  protected $newSet = false; // New set of cards

  protected $deck = null;
  protected $number = null;
  protected $players = null;
  protected $banned = false;

  protected $desc = []; // UI
  protected $costText = ''; // UI
  protected $prerequisite = ''; // UI
  protected $holder = false; // Is holding resources ?
  protected $animalHolder = false; // Is holding animals ?
  protected $field = false; // Is a field card ?

  protected $vp = 0;
  protected $extraVp = false;
  protected $cost = [];
  protected $fee = null;
  protected $exchanges = [];
  protected $category = null;
  protected $type = null;

  protected $passing = false; // only for Minor
  protected $bonusStoneRoom = false; // for C30_HalfTimberedHouse

  protected $actionCard = false; // for C104_Collector
  protected $flow = null;

  public function jsonSerialize()
  {
    $data = parent::jsonSerialize();
    $data['deck'] = $this->deck;
    $data['players'] = $this->players;
    $data['desc'] = $this->desc;
    $data['vp'] = $this->vp;
    $data['extraVp'] = $this->extraVp;
    $data['bonusVp'] = $this->getBonusScore() > 0 ? $this->getBonusScore() : '';
    $data['prerequisite'] = $this->prerequisite;
    $data['costs'] = $this->costs ?? [$this->cost];
    $data['costText'] = $this->costText;
    $data['fee'] = $this->fee;
    $data['type'] = $this->type;
    $data['category'] = $this->category;
    $data['numbering'] = $this->deck . str_pad($this->number, 3, '0', STR_PAD_LEFT);
    $data['holder'] = $this->holder;
    $data['animalHolder'] = $this->animalHolder;
    $data['field'] = $this->field;
    $data['actionCard'] = $this->actionCard;
    return $data;
  }

  public function isSupported($players, $options)
  {
    // Check number of players
    $nPlayers = count($players);
    $checkPlayer =
      $this->players === null ||
      (is_array($this->players) && in_array($nPlayers, $this->players)) || // TODO : we can probably remove that since players criterion are always X+ on cards
      (is_string($this->players) && $nPlayers >= (int) explode('+', $this->players)[0]);

    // Check if deck was enabled
    $checkDeck =
      $this->deck === null ||
      (isset($options[OPTION_DECK_MAPPING[$this->deck]]) &&
        $options[OPTION_DECK_MAPPING[$this->deck]] == OPTION_DECK_ENABLED);

    // Check banlist
    $checkBanlist = !$this->banned || $options[OPTION_COMPETITIVE_LEVEL] != OPTION_COMPETITIVE_BANLIST;

    // Check new set
    $newSetCheck = !$this->newSet || $options[OPTION_NEW_SET] == OPTION_DECK_ENABLED;
    return $this->implemented && $checkPlayer && $checkDeck && $checkBanlist && $newSetCheck;
  }

  public function getType()
  {
    return $this->type;
  }

  public function getCode()
  {
    $map = [
      'A' => 1,
      'B' => 2,
      'C' => 3,
      'D' => 4,
    ];
    return $this->number + 256 * $map[$this->deck];
  }

  /**
   * Useful for notifications
   */
  public function getTypeStr()
  {
    return '';
  }

  public function getScore()
  {
    return $this->vp;
  }

  public function getDeck()
  {
    return $this->deck;
  }
  public function getNumber()
  {
    return $this->number;
  }

  public function isPlayed()
  {
    return $this->location == 'inPlay';
  }

  public function isAnimalHolder()
  {
    return $this->animalHolder;
  }

  public function isField()
  {
    return $this->field;
  }

  public function getPlayer($checkPlayed = false)
  {
    if (!$this->isPlayed() && $checkPlayed) {
      throw new \feException("Trying to get the player for a non-played card : {$this->id}");
    }

    return Players::get($this->pId);
  }

  // Useful for PlayerActionCard
  public function getTurn()
  {
    return null;
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
    Buildings::applyEffects($player, 'ComputeCardCosts', $args);
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

  public function canCook()
  {
    return $this->canExchange(ANYTIME, true);
  }

  public function canBake()
  {
    return $this->canExchange(BREAD, true);
  }

  public function actBuy($player, $args = [])
  {
    // Check $cost
    if (!$this->isBuyable($player, false, $args)) {
      throw new \BgaVisibleSystemException('Cannot be bought. Should not happen');
    }

    // Update stat
    Stats::setCardPlayed($player->getId(), $this->getCode(), Globals::getTurn());

    // Update playerid if major
    $next = null;
    if ($this->passing) {
      if (Globals::isSolo()) {
        self::DB()->update(['player_id' => null, 'card_location' => 'box'], $this->id);
      } else {
        $next = Players::get(Players::getNextId($player));
        self::DB()->update(['player_id' => $next->getId()], $this->id);
      }
    } else {
      $maxPos = self::DB()
        ->where('card_location', 'inPlay')
        ->wherePlayer($player->getId())
        ->max('card_state');
      self::DB()->update(
        [
          'player_id' => $player->getId(),
          'card_location' => 'inPlay',
          'card_state' => $maxPos + 1,
        ],
        $this->id
      );
      $this->location = 'inPlay';
    }
    $this->pId = $player->getId();

    // Notify
    if ($this->passing) {
      if (Globals::isSolo()) {
        Notifications::buyAndDestroyCard($this, $player);
      } else {
        Notifications::buyAndPassCard($this, $player, $next);
      }
    } else {
      Notifications::buyCard($this, $player);
    }

    // Trigger of Pay if needed
    $cost = $this->getCosts($player, $args);
    if ($cost != NO_COST) {
      $player->pay(1, $cost, $this->name);
    }

    // additionnal effects of buying
    if ($this->isField()) {
      $player->board()->addFieldCard($this->id, $this->getFieldDetails());
    }

    $flow = $this->onBuy($player);
    if (!is_null($flow)) {
      Engine::insertAsChild($flow);
    }
  }

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
      ($ctxArgs['cardId'] ?? null) != $this->id;
  }

  protected function isActionCardEvent($event, $actionCardType, $playerConstraint = 'player', $immediately = false)
  {
    return $event['type'] == 'action' &&
      $event['action'] == 'PlaceFarmer' &&
      ($event['actionCardType'] ?? null) == $actionCardType &&
      (is_null($playerConstraint) ||
        ($playerConstraint == 'player' && $this->pId == $event['pId']) ||
        ($playerConstraint == 'opponent' && $this->pId != $event['pId'])) &&
      ((!$immediately && $event['method'] == 'PlaceFarmer') ||
        ($immediately && $event['method'] == 'ImmediatelyAfterPlaceFarmer'));
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

  public function isBonusStoneRoom()
  {
    return $this->bonusStoneRoom;
  }

  public function isActionCard()
  {
    return $this->actionCard;
  }

  public function getMaxBonus($bonusAttribute)
  {
    $player = $this->getPlayer();
    $playedCards = $player->getPlayedCards();
    $score = -1;
    $id = '';
    $f = 'is' . $bonusAttribute;

    foreach ($playedCards as $card) {
      if (!$card->$f()) {
        continue;
      }

      $tmp = $card->computeBonus();
      if ($tmp > $score) {
        $score = $tmp;
        $id = $card->getId();
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

  public function payNode($cost, $sourceName = null, $nb = 1, $to = null)
  {
    return [
      'action' => PAY,
      'args' => [
        'nb' => $nb,
        'costs' => Utils::formatCost($cost),
        'source' => $sourceName ?? $this->name,
        'to' => $to,
      ],
    ];
  }

  public function payGainNode($cost, $gain, $sourceName = null, $optional = true, $pId = null)
  {
    return [
      'type' => NODE_SEQ,
      'optional' => $optional,
      'pId' => $pId ?? $this->pId,
      'childs' => [$this->payNode($cost, $sourceName), $this->gainNode($gain)],
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

  public function bakeBreadNode()
  {
    return [
      'action' => EXCHANGE,
      'optional' => true,
      'pId' => $this->pId,
      'args' => [
        'trigger' => BREAD,
      ],
    ];
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

  public function getNextResource()
  {
    $meeples = Meeples::getResourcesOnCard($this->id);
    return $meeples->empty() ? null : $meeples->last();
  }

  /**********************
   **** FALLBACK LEGACY ******
   **** TODO: REMOVE ********/

  protected function placeMeeplesForFuture($player, $placements, $flagCard = false)
  {
    $currentTurn = Globals::getTurn();

    foreach ($placements as $placement) {
      // Compute the turns where we are going to add stuff, if any
      $turns = [];
      foreach ($placement['turns'] as $turn) {
        if (!\is_int($turn)) {
          // If $x is not int, it's of the form +X
          $turn = (int) substr($turn, 1);
          $turn += $currentTurn;
        }

        if ($turn > $currentTurn && $turn <= 14) {
          $turns[] = $turn;
        }
      }

      if (empty($turns)) {
        continue;
      }

      // Create meeples and place them
      $meepleIds = [];
      foreach ($turns as $turn) {
        foreach ($placement['resources'] as $resType => $amount) {
          array_push(
            $meepleIds,
            ...Meeples::createResourceInLocation(
              $resType,
              'turn_' . $turn,
              $player->getId(),
              $flagCard ? $this->id : null,
              null,
              $amount
            )
          );
        }
      }

      $meeples = Meeples::getMany($meepleIds);
      Notifications::placeMeeplesForFuture($player, $placement['resources'], $turns, $meeples);
    }
  }

  public function onPlayerHarvestEndPhase($player)
  {
    return $this->onPlayerEndHarvest($player);
  }

  public function onPlayerHarvestFeedPhase($player)
  {
    return $this->onPlayerHarvestFeedingPhase($player);
  }

  public function onPlayerHarvestEndOfFeed($player)
  {
    return $this->onPlayerEndHarvestFeedingPhase($player);
  }
}
