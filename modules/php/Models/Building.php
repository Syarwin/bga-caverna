<?php

namespace CAV\Models;

use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Core\Globals;
use CAV\Core\Stats;
use CAV\Helpers\Utils;
use CAV\Managers\Scores;
use CAV\Managers\ActionCards;
use CAV\Managers\Players;
use CAV\Managers\Buildings;
use CAV\Managers\Meeples;
use CAV\Managers\Dwarfs;

/*
 * Building
 */

class Building extends \CAV\Helpers\DB_Model
{
  protected $table = 'buildings';
  protected $primary = 'building_id';
  protected $attributes = [
    'id' => ['building_id', 'int'],
    'location' => 'building_location',
    'state' => ['building_state', 'int'],
    'type' => 'type',
    'pId' => ['player_id', 'int'],
    'extraDatas' => ['extra_datas', 'obj'],
    'x' => ['x', 'int'],
    'y' => ['y', 'int'],
  ];

  /*
   * STATIC INFORMATIONS
   *  they are overwritten by children
   */
  protected $staticAttributes = ['name', 'desc', 'tooltip', 'vp', 'category', 'dwelling', 'animalHolder', 'beginner'];
  protected $name = ''; // UI
  protected $desc = []; // UI
  protected $tooltip = [];
  protected $vp = 0;
  protected $category = null; // Useful for location on board
  protected $dwelling = 0;
  protected $animalHolder = false;
  protected $beginner = false;

  public function jsonSerialize()
  {
    $data = parent::jsonSerialize();
    //    $data['bonusVp'] = $this->getBonusScore() > 0 ? $this->getBonusScore() : '';
    return $data;
  }

  public function getPos()
  {
    return [
      'x' => $this->getX(),
      'y' => $this->getY(),
    ];
  }

  public function getPlayer($checkPlayed = false)
  {
    if (!$this->isPlayed() && $checkPlayed) {
      throw new \feException("Trying to get the player for a non-played card : {$this->id}");
    }

    return Players::get($this->pId);
  }

  public function enforceReorganizeOnLastHarvest()
  {
    return false;
  }

  public function isConsideredDwelling()
  {
    return false;
  }

  //////////////////////////////////
  //  ____       _
  // / ___|  ___| |_ _   _ _ __
  // \___ \ / _ \ __| | | | '_ \
  // ___) |  __/ |_| |_| | |_) |
  // |____/ \___|\__|\__,_| .__/
  //                      |_|
  //////////////////////////////////
  protected $implemented = true; // For DEV only
  protected $isNotBeginner = false;
  protected $banned = false;

  public function isSupported($players, $options)
  {
    // Check banlist
    // $checkBanlist = !$this->banned || $options[OPTION_COMPETITIVE_LEVEL] != OPTION_COMPETITIVE_BANLIST;

    if ($options[OPTION_COMPETITIVE_LEVEL] == OPTION_COMPETITIVE_BEGINNER) {
      return $this->implemented && $this->beginner;
    } else {
      return $this->implemented;
    }
  }

  //////////////////////////////////////////////
  //  _____                 _     _
  // |  ___|   _ _ __ _ __ (_)___| |__
  // | |_ | | | | '__| '_ \| / __| '_ \
  // |  _|| |_| | |  | | | | \__ \ | | |
  // |_|   \__,_|_|  |_| |_|_|___/_| |_|
  //
  //////////////////////////////////////////////

  protected $cost = [];

  public function isPlayed()
  {
    return $this->location == 'board' || $this->location == 'inPlay';
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

    if (isset($args['costs']) && $args['costs'] != null) {
      $costs['trades'][] = $args['costs'];
    }

    foreach ($this->getBaseCosts() as $cost) {
      $costs['trades'][] = array_merge(['max' => 1], $cost);
    }

    // Apply card effects
    $args['card'] = $this;
    $args['costs'] = $costs;
    Buildings::applyEffects($player, 'ComputeCostsFurnish', $args);
    return $args['costs'];
  }

  public function isBuyable($player, $ignoreResources = false, $args = [])
  {
    $cost = $this->getCosts($player, $args);
    return $ignoreResources || $player->canBuy($cost);
  }

  public function actFurnish($player, $x, $y, $args = [])
  {
    // Check $cost
    if (!$this->isBuyable($player, false, $args)) {
      throw new \BgaVisibleSystemException('Cannot be bought. Should not happen');
    }

    // get Costs before placing the building
    $cost = $this->getCosts($player, $args);

    // Update stat
    // TODO
    //Stats::setCardPlayed($player->getId(), $this->getCode(), Globals::getTurn());

    // Update location
    $this->setPId($player->getId());
    $this->setLocation('inPlay');
    $this->setX($x);
    $this->setY($y);

    if ($cost != NO_COST) {
      $player->pay(1, $cost, $this->name);
    }

    // Additionnal effects of buying
    $flow = $this->onBuy($player, $args);
    if (!is_null($flow)) {
      Engine::insertAsChild($flow);
    }
  }

  protected function onBuy($player, $eventData)
  {
    return null;
  }

  ///////////////////////////////////////////////////////////
  //  _____          _
  // | ____|_  _____| |__   __ _ _ __   __ _  ___  ___
  // |  _| \ \/ / __| '_ \ / _` | '_ \ / _` |/ _ \/ __|
  // | |___ >  < (__| | | | (_| | | | | (_| |  __/\__ \
  // |_____/_/\_\___|_| |_|\__,_|_| |_|\__, |\___||___/
  //                                   |___/
  ///////////////////////////////////////////////////////////

  protected $exchanges = [];

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

  ///////////////////////////////////////
  //  ____
  // / ___|  ___ ___  _ __ ___  ___
  // \___ \ / __/ _ \| '__/ _ \/ __|
  //  ___) | (_| (_) | | |  __/\__ \
  // |____/ \___\___/|_|  \___||___/
  //
  ///////////////////////////////////////
  protected function addScoringEntry($score, $isBonus = true, $player = null)
  {
    if (!$this->isPlayed()) {
      throw new \feException("Trying to addScoringEntry for a non-played card : {$this->id}");
    }

    $player = $player ?? $this->getPlayer();
    $desc['args']['i18n'][] = 'card_name';
    $desc['args']['card_name'] = $this->getName();
    $desc['args']['player_name'] = $player ?? $player->getName();
    $desc['args']['score'] = $score;
    Scores::addEntry($player, $isBonus ? SCORING_BUILDINGS_BONUS : SCORING_BUILDINGS, $score, null, $this->getName());
  }

  protected function addBonusScoringEntry($score, $player = null)
  {
    $this->addScoringEntry($score, true, $player);
  }

  public function computeScore()
  {
    if ($this->vp != 0) {
      $this->addScoringEntry($this->vp, false);
    }

    $this->computeBonusScore();
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

  //////////////////////////////////////////////////////////////////////////////
  //  _     _     _                         _   _      _
  // | |   (_)___| |_ ___ _ __   ___ _ __  | | | | ___| |_ __   ___ _ __ ___
  // | |   | / __| __/ _ \ '_ \ / _ \ '__| | |_| |/ _ \ | '_ \ / _ \ '__/ __|
  // | |___| \__ \ ||  __/ | | |  __/ |    |  _  |  __/ | |_) |  __/ |  \__ \
  // |_____|_|___/\__\___|_| |_|\___|_|    |_| |_|\___|_| .__/ \___|_|  |___/
  //                                                    |_|
  //////////////////////////////////////////////////////////////////////////////

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

  /////////////////////////////////////////////////////////////////////////////////
  //  ____              _             _        ____
  // / ___| _   _ _ __ | |_ __ ___  _(_) ___  / ___| _   _  __ _  __ _ _ __
  // \___ \| | | | '_ \| __/ _` \ \/ / |/ __| \___ \| | | |/ _` |/ _` | '__|
  //  ___) | |_| | | | | || (_| |>  <| | (__   ___) | |_| | (_| | (_| | |
  // |____/ \__, |_| |_|\__\__,_/_/\_\_|\___| |____/ \__,_|\__, |\__,_|_|
  //        |___/                                          |___/
  /////////////////////////////////////////////////////////////////////////////////

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

  public function isFlagged($data = 'used')
  {
    return $this->getExtraDatas($data) ?? 0;
  }
}
