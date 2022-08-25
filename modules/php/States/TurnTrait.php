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
    // 0) Make children grow up
    // $children = Dwarfs::growChildren();
    // if (!empty($children)) {
    //   Notifications::growChildren($children);
    //   Notifications::updateHarvestCosts();
    // }

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
    Notifications::startNewTurn($turn);

    // Reveal new action card
    $card = ActionCards::draw()->first();
    Globals::setLastRevealed($card->getId());
    Notifications::revealActionCard($card);

    // Reveal harvest token if needed
    $harvest = Meeples::getHarvestToken();
    if ($harvest != null) {
      Meeples::DB()->update(['meeple_state', 1], $harvest['id']);
      $hToken = Meeples::get($harvest['id']);
      Notification::revealHarvestToken(
        $hToken,
        Meeples::getFilteredQuery(null, 'turn_' . $turn, [\HARVEST_RED])
          ->where('meeple_state', 1)
          ->get()
          ->count()
      );
    }

    // Listen for buildings AfterRevealAction
    $this->checkBuildingListeners('AfterRevealAction', 'stPreparationListener');
  }

  function stPreparationListener()
  {
    $this->checkBuildingListeners('Preparation', 'stPreparation');
  }

  /**
   * Prepare the current turn
   */
  function stPreparation()
  {
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
   ********** FLOW CHOICE *********
   ********************************
   ********************************/
  function argsResolveChoice()
  {
    $player = Players::getActive();
    $args = [
      'choices' => Engine::getNextChoice($player),
      'allChoices' => Engine::getNextChoice($player, true),
      'previousEngineChoices' => Globals::getEngineChoices(),
    ];
    $this->addArgsAnytimeAction($args, 'resolveChoice');
    return $args;
  }

  function actChooseAction($choiceId)
  {
    $player = Players::getActive();
    Engine::chooseNode($player, $choiceId);
  }

  public function stResolveStack()
  {
  }

  public function stResolveChoice()
  {
    if ($this->takeIndependentMandatoryAnytimeActionIfAny()) {
      return;
    }
  }

  function argsImpossibleAction()
  {
    $player = Players::getActive();
    $node = Engine::getNextUnresolved();

    $args = [
      'desc' => $node->getDescription(),
      'previousEngineChoices' => Globals::getEngineChoices(),
    ];
    $this->addArgsAnytimeAction($args, 'impossibleAction');
    return $args;
  }

  /*******************************
   ******* CONFIRM / RESTART ******
   ********************************/
  public function argsConfirmTurn()
  {
    $data = [
      'previousEngineChoices' => Globals::getEngineChoices(),
      'automaticAction' => false,
    ];
    $this->addArgsAnytimeAction($data, 'confirmTurn');
    return $data;
  }

  public function stConfirmTurn()
  {
    if ($this->takeIndependentMandatoryAnytimeActionIfAny()) {
      return;
    }

    // Check user preference to bypass if DISABLED is picked
    $pref = Players::getActive()->getPref(OPTION_CONFIRM);
    if ($pref == OPTION_CONFIRM_DISABLED) {
      $this->actConfirmTurn();
    }
  }

  public function actConfirmTurn()
  {
    self::checkAction('actConfirmTurn');
    Engine::confirm();
  }

  public function actConfirmPartialTurn()
  {
    self::checkAction('actConfirmPartialTurn');
    Engine::confirmPartialTurn();
  }

  public function actRestart()
  {
    self::checkAction('actRestart');
    if (Globals::getEngineChoices() < 1) {
      throw new \BgaVisibleSystemException('No choice to undo');
    }
    Engine::restart();
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
    $harvestToken = Meeples::getHarvestToken();
    Globals::setHarvestCost(2);

    if (
      $turn == 3 ||
      $turn == 5 ||
      (($turn > 5 && $harvestToken['type'] == HARVEST_GREEN) ||
        Meeples::getFilteredQuery(null, 'turn_' . $turn, [\HARVEST_RED])
          ->where('meeple_state', 1)
          ->get()
          ->count() == 3)
    ) {
      $this->checkBuildingListeners('BeforeHarvest', ST_START_HARVEST);
      return;
    } elseif (
      $turn == 4 ||
      Meeples::getFilteredQuery(null, 'turn_' . $turn, [\HARVEST_RED])
        ->where('meeple_state', 1)
        ->get()
        ->count() == 2
    ) {
      Globals::setHarvestCost(1);
      $this->initCustomTurnOrder('harvestFeed', \HARVEST, ST_HARVEST_FEED, 'stHarvestEnd');
    } else {
      $this->gamestate->nextState('end');
    }
  }

  function stEndOfTurn()
  {
    if (Globals::isHarvest()) {
      Globals::setSkipHarvest([]);
    }

    Globals::setHarvest(false);
    if (Globals::getTurn() == 12 || (Players::count() <= 2 && Globals::getTurn() == 11)) {
      $this->gamestate->nextState('end');
      return;
    }

    // Pig Breeder
    // if (Globals::getTurn() == 12) {
    //   $card = Buildings::getSingle('A165_PigBreeder', false);
    //   if ($card != null && $card->isPlayed()) {
    //     $player = $card->getPlayer();
    //     if ($player->breed(PIG, clienttranslate("Pig breeder's effect"))) {
    //       // Inserting leaf REORGANIZE
    //       Engine::setup(
    //         [
    //           'pId' => $player->getId(),
    //           'action' => REORGANIZE,
    //           'args' => [
    //             'trigger' => HARVEST,
    //             'breedTypes' => [PIG => true],
    //           ],
    //         ],
    //         ['state' => ST_BEFORE_START_OF_TURN]
    //       );
    //       Engine::proceed();
    //       return;
    //     }
    //   }
    // }

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
    Globals::setTurn(15);
    Globals::setLiveScoring(true);
    Scores::update(true);
    Notifications::seed(Globals::getGameSeed());
    $this->gamestate->jumpToState(\ST_END_GAME);
  }
}
