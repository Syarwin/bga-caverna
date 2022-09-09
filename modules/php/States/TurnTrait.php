<?php
namespace CAV\States;
use CAV\Core\Globals;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Core\Stats;
use CAV\Managers\Players;
use CAV\Managers\ActionCards;
use CAV\Managers\Meeples;
use CAV\Managers\Dwarfs;
use CAV\Managers\Scores;
use CAV\Managers\Actions;
use CAV\Managers\Buildings;

trait TurnTrait
{
  /**
   * State function when starting a turn
   *  useful to intercept for some buildings that happens at that moment
   */
  function stBeforeStartOfTurn()
  {
    Globals::setRubyChoice([]);
    // 0) Make children grow up
    $children = Dwarfs::growChildren();
    if (!empty($children)) {
      Notifications::growChildren($children);
    }

    Notifications::updateHarvestCosts();
    $skipped = Players::getAll()
      ->filter(function ($player) {
        return $player->isZombie();
      })
      ->getIds();
    Globals::setSkippedPlayers($skipped);

    // 1) Listen for buildings BeforeStartOfTurn
    $this->checkBuildingListeners('BeforeStartOfTurn', 'stPreparationRevealAction');
  }

  /**
   * Prepare the current turn
   */
  function stPreparationRevealAction()
  {
    $turn = Globals::incTurn();
    Notifications::startNewRound($turn);

    // Reveal new action card
    $card = ActionCards::draw()->first();
    Globals::setLastRevealed($card->getId());
    Notifications::revealActionCard($card);

    if ($card->getId() == 'ActionFamilyLife') {
      $oldCard = ActionCards::get('ActionWishChildren');
      ActionCards::DB()->update(['card_id' => 'ActionUrgentWishChildren'], 'ActionWishChildren');
      $newCard = ActionCards::get('ActionUrgentWishChildren');
      Notifications::flipWishChildren($oldCard, $newCard);
    }

    if (Globals::isRevealStartHarvest()) {
      Meeples::revealHarvestToken();
    }

    // Listen for buildings AfterRevealAction
    $this->checkBuildingListeners('AfterRevealAction', 'stPreparationListener');
  }

  function stPreparationListener()
  {
    if (Globals::isSolo() && Players::get(Globals::getFirstPlayer())->hasRuby()) {
      //Check all accumulations spaces with more than 6 goods
      if (count(ActionCards::getAccumulationSpacesWith6()) != 0) {
        $this->initCustomDefaultTurnOrder('soloAccumulation', ST_RUBY_CHOICE, 'stPreparation');
        return;
      }
    }
    $this->checkBuildingListeners('Preparation', 'stPreparation');
  }

  /**
   *
   * Solo Ruby choice
   */
  function argsRubyChoice()
  {
    $player = Players::getCurrent();
    return ['cards' => ActionCards::getAccumulationSpacesWith6(), 'rubies' => $player->countReserveResource(RUBY)];
  }

  function actRubyChoice($cards)
  {
    self::checkAction('actRubyChoice');
    $player = Players::getActive();
    $args = $this->argsRubyChoice();

    if (count($cards) > $args['rubies']) {
      throw new \BgaVisibleSystemException('More spaces selected than rubies. Should not happen');
    }

    foreach ($cards as $cardId) {
      if (!in_array($cardId, $args['cards'])) {
        throw new \BgaVisibleSystemException('Invalid space. Should not happen');
      }
    }
    Globals::setRubyChoice($cards);
    $deleted = $player->useResource(RUBY, count($cards));
    Notifications::payResources($player, $deleted, clienttranslate('keeping accumulation spaces full'));
    $this->gamestate->nextState('preparation');
  }

  function actPassRuby()
  {
    self::checkAction('actRubyChoice');
    $this->gamestate->nextState('preparation');
  }

  /**
   * Prepare the current turn
   */
  function stPreparation()
  {
    // Solo: clear all spots with more than 6 goods and not saved by a ruby
    if (Globals::isSolo()) {
      $rubyChoice = Globals::getRubyChoice();
      $deleted = [];
      foreach (ActionCards::getAccumulationSpacesWith6() as $cardId) {
        if (!in_array($cardId, $rubyChoice)) {
          foreach (Meeples::getResourcesOnCard($cardId) as $mId => $m) {
            $deleted[] = $m;
            Meeples::DB()->delete($mId);
          }
        }
      }
      if (!empty($deleted)) {
        Notifications::clearActionSpaces($deleted);
      }
    }

    // Fill up accumulation spots
    $resourceIds = ActionCards::accumulate();
    Notifications::accumulate(Meeples::getMany($resourceIds));

    // Change first player and check start of turn trigger
    $firstPlayer = Globals::getFirstPlayer();
    Stats::incFirstPlayer($firstPlayer);
    $this->initCustomDefaultTurnOrder('startOfTurn', 'stStartOfTurn', 'stPreStartofWorkPhase');
  }

  /*
   *  c) Collect players' resources on action buildings
   */
  function stStartOfTurn()
  {
    $pId = Players::getActiveId();
    $turn = Globals::getTurn();

    // Get triggered buildings
    $event = [
      'type' => 'StartOfTurn',
      'method' => 'StartOfTurn',
      'pId' => $pId,
    ];
    $reaction = Buildings::getReaction($event, false);

    // Get meeple to receive
    $resources = Meeples::getResourcesOnCard('turn_' . $turn, $pId);
    foreach ($resources as $id => $res) {
      $reaction['childs'][] = [
        'action' => RECEIVE,
        'args' => [
          'meeple' => $id,
        ],
      ];
    }

    if (empty($reaction['childs'])) {
      // No reaction => just go to next player
      $this->nextPlayerCustomOrder('startOfTurn');
    } else {
      // Reaction => boot up the Engine
      Engine::setup($reaction, ['method' => 'stClearStartOfTurn']);
      Engine::proceed();
    }
  }

  /**
   * Clear potential meeples that were left on the card by the player
   */
  function stClearStartOfTurn()
  {
    $pId = Players::getActiveId();
    $turn = Globals::getTurn();

    // Delete any remeaning meeples
    $resources = Meeples::getResourcesOnCard('turn_' . $turn, $pId);
    if ($resources->count() > 0) {
      foreach ($resources as $id => $res) {
        Meeples::DB()->delete($id);
      }
      Notifications::silentKill($resources->toArray());
    }

    $this->nextPlayerCustomOrder('startOfTurn');
  }

  function stPreStartofWorkPhase()
  {
    $this->initCustomDefaultTurnOrder('startOfWork', 'stStartofWorkPhase', 'stStartLaborDay');
  }

  function stStartofWorkPhase()
  {
    $pId = Players::getActiveId();
    $turn = Globals::getTurn();

    // Get triggered buildings
    $event = [
      'type' => 'startOfWork',
      'method' => 'startOfWork',
      'pId' => $pId,
    ];
    $reaction = Buildings::getReaction($event, false);

    if (empty($reaction['childs'])) {
      // No reaction => just go to next player
      $this->nextPlayerCustomOrder('startOfWork');
    } else {
      // Reaction => boot up the Engine
      Engine::setup($reaction, ['order' => 'startOfWork']);
      Engine::proceed();
    }
  }

  function stStartLaborDay()
  {
    Globals::setWorkPhase(true);

    // Change first player and start labor
    $this->initCustomDefaultTurnOrder('labor', ST_LABOR, ST_END_WORK_PHASE, true);
  }

  /**
   * Activate next player with a farmer available
   */
  function stLabor()
  {
    $player = Players::getActive();
    Globals::setBreed([]);

    // Already out of round ? => Go to the next player if one is left
    $skipped = Globals::getSkippedPlayers();
    if (in_array($player->getId(), $skipped)) {
      // Everyone is out of round => end it
      $remaining = array_diff(Players::getAll()->getIds(), $skipped);
      if (empty($remaining)) {
        $this->endCustomOrder('labor');
      } else {
        $this->nextPlayerCustomOrder('labor');
      }
      return;
    }

    // No dwarf to allocate ?
    if (!$player->hasDwarfAvailable()) {
      $skipped[] = $player->getId();
      Globals::setSkippedPlayers($skipped);
      $this->nextPlayerCustomOrder('labor');
      return;
    }

    self::giveExtraTime($player->getId());

    $args = [];
    Buildings::applyEffects($player, 'resetFlags', $args);

    $node = [
      'action' => PLACE_DWARF,
      'pId' => $player->getId(),
    ];

    // Inserting leaf PLACE_DWARF
    Engine::setup($node, ['order' => 'labor']);
    Engine::proceed();
  }

  /********************************
   ********************************
   ********** END OF TURN *********
   ********************************
   ********************************/
  function stEndWorkPhase()
  {
    // Listen for buildings onEndWorkPhase
    $this->checkBuildingListeners('EndWorkPhase', 'stStartReturnHome');
  }

  function stStartReturnHome()
  {
    // Listen for buildings onStartReturnHome
    $this->checkBuildingListeners('StartReturnHome', 'stReturnHome');
  }

  function stReturnHome()
  {
    Players::returnHome();
    Notifications::returnHome(Dwarfs::getAllAvailable());
    Globals::setWorkPhase(false);

    // Listen for buildings onReturnHome
    $this->checkBuildingListeners('ReturnHome', ST_PRE_END_OF_TURN);
  }

  function stPreEndOfTurn()
  {
    // Next turn or harvest
    $turn = Globals::getTurn();
    Globals::setHarvestCost(2);
    if (Globals::isRevealStartHarvest()) {
      Meeples::revealHarvestToken();
    }

    $this->checkBuildingListeners('BeforeHarvest', ST_START_HARVEST);
  }

  function stEndOfTurn()
  {
    $token = Meeples::endHarvest();
    Notifications::endHarvest($token);

    if (Globals::isHarvest()) {
      Globals::setSkipHarvest([]);
    }

    Globals::setHarvest(false);
    if (Globals::getTurn() == 12 || (Players::count() <= 2 && Globals::getTurn() == 11)) {
      $this->gamestate->nextState('end');
      return;
    }

    $this->gamestate->nextState('newTurn');
  }

  function stPreEndOfGame()
  {
    $this->checkBuildingListeners('BeforeEndOfGame', 'stLaunchEndOfGame');
  }

  function stLaunchEndOfGame()
  {
    foreach (Buildings::getAllBuildingsWithMethod('EndOfGame') as $card) {
      $card->onEndOfGame();
    }
    Globals::setTurn(12);
    Globals::setLiveScoring(true);
    Scores::update(true);
    // Notifications::seed(Globals::getGameSeed());
    $this->gamestate->jumpToState(\ST_END_GAME);
  }
}
