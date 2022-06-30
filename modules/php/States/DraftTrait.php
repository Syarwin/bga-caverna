<?php
namespace AGR\States;
use AGR\Core\Globals;
use AGR\Managers\ActionCards;
use AGR\Managers\PlayerCards;
use AGR\Managers\Players;
use AGR\Helpers\DB_Manager;
use AGR\Core\Notifications;
use AGR\Core\Stats;

trait DraftTrait
{
  function actOrderCards($cardIds)
  {
    $player = Players::getCurrent();
    foreach ($cardIds as $i => $cardId) {
      $card = PlayerCards::getSingle($cardId);
      if (is_null($card) || $card->isPlayed() || $card->getPId() != $player->getId()) {
        throw new \BgaVisibleSystemException("You can't reorder that card:" . $card->getId());
      }

      PlayerCards::setState($cardId, $i);
    }
  }

  /**
   * Starting number of cards depending on each round
   */
  function getDraftStartingNumberOfCards()
  {
    $map = [
      OPTION_PICK_7_OUT_OF_10 => 10,
      OPTION_DRAFT_7_SIMULTANEOUS => 7,
      OPTION_DRAFT_8_SIMULTANEOUS => 8,
      OPTION_DRAFT_9_SIMULTANEOUS => 9,
      OPTION_DRAFT_10_SIMULTANEOUS => 10,
      OPTION_DRAFT_7_OCCUPATIONS => 7,
      OPTION_DRAFT_8_OCCUPATIONS => 8,
      OPTION_DRAFT_9_OCCUPATIONS => 9,
      OPTION_DRAFT_10_OCCUPATIONS => 10,
      OPTION_DRAFT_7_MINORS => 7,
      OPTION_DRAFT_8_MINORS => 8,
      OPTION_DRAFT_9_MINORS => 9,
      OPTION_DRAFT_10_MINORS => 10,
      OPTION_DRAFT_FREE => -1,
      OPTION_SEED_MODE => 0,
    ];

    return $map[Globals::getDraftMode()] ?? 0;
  }

  /**
   * Protocol of draft
   */
  function getDraftProtocol()
  {
    $map = [
      OPTION_PICK_7_OUT_OF_10 => ONE_SHOT,
      OPTION_DRAFT_FREE => ONE_SHOT,
      OPTION_DRAFT_7_SIMULTANEOUS => SIMULTANEOUS,
      OPTION_DRAFT_8_SIMULTANEOUS => SIMULTANEOUS,
      OPTION_DRAFT_9_SIMULTANEOUS => SIMULTANEOUS,
      OPTION_DRAFT_10_SIMULTANEOUS => SIMULTANEOUS,
      OPTION_DRAFT_7_OCCUPATIONS => OCCUPATION_FIRST,
      OPTION_DRAFT_8_OCCUPATIONS => OCCUPATION_FIRST,
      OPTION_DRAFT_9_OCCUPATIONS => OCCUPATION_FIRST,
      OPTION_DRAFT_10_OCCUPATIONS => OCCUPATION_FIRST,
      OPTION_DRAFT_7_MINORS => MINOR_FIRST,
      OPTION_DRAFT_8_MINORS => MINOR_FIRST,
      OPTION_DRAFT_9_MINORS => MINOR_FIRST,
      OPTION_DRAFT_10_MINORS => MINOR_FIRST,
    ];

    return $map[Globals::getDraftMode()] ?? null;
  }

  /**
   * Get total number of rounds
   */
  function getDraftTotalNumberOfTurns()
  {
    $protocol = $this->getDraftProtocol();
    $map = [
      ONE_SHOT => 1,
      SIMULTANEOUS => 7,
    ];
    return $map[$protocol] ?? 14;
  }

  /**
   * Give draft type for current round
   */
  function getDraftType()
  {
    $protocol = $this->getDraftProtocol();
    $turn = Globals::getDraftTurn();

    $res = [
      OCCUPATION => 0,
      MINOR => 0,
    ];
    if ($protocol == ONE_SHOT) {
      $res[OCCUPATION] = 7;
      $res[MINOR] = 7;
    } elseif ($protocol == SIMULTANEOUS) {
      $res[OCCUPATION] = 1;
      $res[MINOR] = 1;
    } elseif (($protocol == OCCUPATION_FIRST && $turn <= 7) || ($protocol == MINOR_FIRST && $turn > 7)) {
      $res[OCCUPATION] = 1;
    } elseif (($protocol == MINOR_FIRST && $turn <= 7) || ($protocol == OCCUPATION_FIRST && $turn > 7)) {
      $res[MINOR] = 1;
    }
    return $res;
  }

  /**
   * Entering a new round of draft
   */
  function stDraftGame()
  {
    // If draft is disabled, skip this phase
    if (Globals::getDraftMode() == OPTION_DRAFT_DISABLED) {
      // Update stat with draftPos = 0
      foreach (Players::getAll() as $pId => $player) {
        foreach ($player->getHand() as $card) {
          Stats::setNextCard($pId, $card->getCode(), 0);
        }
      }

      $this->saveSeed();
      $this->gamestate->nextState('noDraft');
      return;
    }
    // If "load seed" mode is selected, skip to this phase
    if (Globals::getDraftMode() == OPTION_SEED_MODE) {
      $this->gamestate->setAllPlayersMultiactive();
      $this->gamestate->nextState('seed');
      return;
    }

    // Check if end of Draft
    $turn = Globals::incDraftTurn();
    $totalTurns = $this->getDraftTotalNumberOfTurns();
    if ($turn > $totalTurns) {
      // Update stat with potential remeaning cards
      foreach (Players::getAll() as $pId => $player) {
        $cards = PlayerCards::getSelectQuery()
          ->wherePlayer($pId)
          ->where('card_location', 'passing')
          ->get();
        foreach ($cards as $card) {
          Stats::setNextDiscardedCard($pId, $card->getCode());
        }
      }

      Notifications::draftIsOver();
      $this->saveSeed();
      $this->gamestate->nextState('startTurn');
      return;
    }

    // Switch cards
    PlayerCards::passCards();

    // Enable players and give extra time
    $players = Players::getAll()->getIds();
    foreach ($players as $pId) {
      $this->giveExtraTime($pId, 90);
    }
    $this->gamestate->setPlayersMultiactive($players, 'draft');
    $this->gamestate->nextState('draft');
  }

  /**
   * Compute the available pool of cards for each player
   */
  function argsDraftPlayers()
  {
    $type = $this->getDraftType();

    $args = [];
    foreach (Players::getAll() as $pId => $player) {
      $args[$pId] = $player
        ->getCards()
        ->filter(function ($card) use ($type) {
          return in_array($card->getLocation(), ['draft', 'selection']) && $type[$card->getType()] > 0;
        })
        ->toArray();
    }

    // Compute correct description
    $choice = [
      'log' => '',
      'args' => [
        'minor' => $type[MINOR],
        'occupation' => $type[OCCUPATION],
      ],
    ];
    if ($type[OCCUPATION] > 0 && $type[MINOR] > 0) {
      $choice['log'] = clienttranslate('${minor} minor improvement(s) and ${occupation} occupation(s)');
    } elseif ($type[OCCUPATION] > 0) {
      $choice['log'] = $type[OCCUPATION] == 1 ? clienttranslate('an occupation') : '${occupation} occupations';
    } elseif ($type[MINOR] > 0) {
      $choice['log'] = $type[MINOR] == 1 ? clienttranslate('a minor improvement') : '${minor} minor improvements';
    }

    // Avoid notification size limit
    if (Globals::getDraftMode() == OPTION_DRAFT_FREE) {
      $args = [];
    }

    return [
      '_private' => $args,
      'type' => $type,
      'i18n' => 'draftChoice',
      'draftChoice' => $choice,
      'turn' => Globals::getDraftTurn(),
      'total' => $this->getDraftTotalNumberOfTurns(),
    ];
  }

  function stDraftPlayers()
  {
    $turn = Globals::getDraftTurn();
    $totalTurns = $this->getDraftTotalNumberOfTurns();

    // If draft 7 and last round => auto draft
    if ($this->getDraftStartingNumberOfCards() == 7 && $turn == $totalTurns) {
      $args = $this->argsDraftPlayers();
      foreach (Players::getAll() as $pId => $player) {
        foreach ($args['_private'][$pId] as $card) {
          $this->actDraftAdd($card->getId(), $player);
        }
      }
      $this->gamestate->nextState('apply');
    }
  }

  /**
   * Add a card to the draft selection
   */
  public function actDraftAdd($cardId, $player = null)
  {
    $card = PlayerCards::get($cardId);
    $player = $player ?? Players::getCurrent();

    // Check card is in hand
    if ($card->getPId() != $player->getId()) {
      throw new \BgaVisibleSystemException('Card is not in hand');
    }

    if ($card->getLocation() != 'draft') {
      throw new \BgaVisibleSystemException('Card has already been selected');
    }

    $pos = PlayerCards::addToSelection($card);
    $check = $this->checkDraftSelection();
    if ($check == -1) {
      throw new \BgaVisibleSystemException('Too many cards drafted. Should not happen');
    }

    Notifications::addCardToDraftSelection($player, $card, $pos);
  }

  /**
   * Check the draft selection of current player
   *  => return -1 if invalid, 0 if ok, 1 if fullfilled
   */
  public function checkDraftSelection($player = null)
  {
    $player = $player ?? Players::getCurrent();
    $selection = $player->getDraftSelection();
    $selectionByType = $selection->reduce(
      function ($res, $card) {
        $res[$card->getType()]++;
        return $res;
      },
      [MINOR => 0, OCCUPATION => 0]
    );

    $type = $this->getDraftType();
    if ($type[MINOR] < $selectionByType[MINOR] || $type[OCCUPATION] < $selectionByType[OCCUPATION]) {
      return -1;
    } elseif ($type[MINOR] == $selectionByType[MINOR] && $type[OCCUPATION] == $selectionByType[OCCUPATION]) {
      return 1;
    } else {
      return 0;
    }
  }

  /**
   * Remove a card to the draft selection
   */
  public function actDraftRemove($cardId)
  {
    $card = PlayerCards::get($cardId);
    $player = Players::getCurrent();

    // Check card is in hand
    if ($card->getPId() != $player->getId()) {
      throw new \BgaVisibleSystemException('Card is not in hand');
    }

    if ($card->getLocation() != 'selection') {
      throw new \BgaVisibleSystemException('Card has not already been selected');
    }

    PlayerCards::removeFromSelection($card);
    Notifications::removeCardFromDraftSelection($player, $card);
    $this->gamestate->setPlayersMultiactive([$player->getId()], '');
  }

  /**
   * Confirm a draft selection => make the player inactive
   */
  public function actDraftConfirm()
  {
    $player = Players::getCurrent();
    $check = $this->checkDraftSelection($player);
    if ($check != 1) {
      throw new \BgaVisibleSystemException(
        'Your draft selection is not valid: ' . $check == -1 ? 'too many card drafted' : 'not enough card drafterd'
      );
    }
    $this->gamestate->setPlayerNonMultiactive($player->getId(), 'apply');
  }

  /**
   * Add selected card to hand of players
   */
  public function stApplyDraft()
  {
    // Sanity checks
    foreach (Players::getAll() as $player) {
      if ($this->checkDraftSelection($player) != 1) {
        throw new \BgaVisibleSystemException('Draft selection of player #' . $player->getId() . ' is not valid');
      }
    }

    // Update stats
    $turn = Globals::getDraftTurn();
    foreach (Players::getAll() as $pId => $player) {
      foreach ($player->getDraftSelection() as $card) {
        Stats::setNextCard($pId, $card->getCode(), $turn);
      }
    }

    // Update cards
    $cards = PlayerCards::confirmDraftSelections();
    foreach ($cards as $card) {
      Notifications::confirmDraftSelection($card);
    }
    Notifications::clearDraftPools();
    $this->gamestate->nextState('draft');
  }

  /**
   * Save/load seed
   */
  public function saveSeed()
  {
    $raw = Players::count() . '|' . ActionCards::getSeed() . '|' . PlayerCards::getSeed();
    $encoded = rtrim(strtr(base64_encode(addslashes(gzcompress($raw, 9))), '+/', '-_'), '=');
    Globals::setGameSeed($encoded);
  }

  public function actLoadSeed($seed)
  {
    $raw = gzuncompress(
      stripslashes(base64_decode(str_pad(strtr($seed, '-_', '+/'), strlen($seed) % 4, '=', STR_PAD_RIGHT)))
    );
    $data = explode('|', $raw);
    if ($data[0] != Players::count()) {
      throw new \BgaUserException(
        'Trying to load a ' . $data[0] . ' players seed in your ' . Players::count() . ' players game'
      );
    }

    // Load action cards
    ActionCards::setSeed($data[1]);

    // Load player cards
    $i = 2;
    PlayerCards::preSeedClear();
    foreach (Players::getAll() as $player) {
      PlayerCards::setSeed($player, $data[$i++]);
    }

    // Refresh UI
    $datas = $this->getAllDatas();
    Notifications::refreshUI($datas);
    foreach (Players::getAll() as $player) {
      Notifications::refreshHand($player, $player->getHand()->ui());
    }

    // Start game
    $this->gamestate->nextState('start');
  }
}
