<?php
namespace CAV\States;
use CAV\Core\Globals;
use CAV\Managers\Players;
use CAV\Managers\Meeples;
use CAV\Managers\Fences;
use CAV\Managers\Actions;
use CAV\Managers\Buildings;
use CAV\Core\Engine;
use CAV\Core\Game;
use CAV\Models\PlayerBoard;
use CAV\Core\Notifications;
use CAV\Helpers\Utils;

trait ActionTrait
{
  /**
   * Trying to get the atomic action corresponding to the state where the game is
   */
  function getCurrentAtomicAction()
  {
    $stateId = $this->gamestate->state_id();
    return Actions::getActionOfState($stateId);
  }

  /**
   * Ask the corresponding atomic action for its args
   */
  function argsAtomicAction()
  {
    $player = Players::getActive();
    $action = $this->getCurrentAtomicAction();
    $node = Engine::getNextUnresolved();
    $args = Actions::getArgs($action, $node);
    $args['automaticAction'] = Actions::get($action, $node)->isAutomatic($player);
    $args['previousEngineChoices'] = Globals::getEngineChoices();
    $this->addArgsAnytimeAction($args, $action);

    $sourceId = $node->getSourceId() ?? null;
    if (!isset($args['source']) && !is_null($sourceId)) {
      $args['source'] = Buildings::get($sourceId)->getName();
    }

    return $args;
  }

  /**
   * Add anytime actions
   */
  function addArgsAnytimeAction(&$args, $action)
  {
    // If the action is auto => don't display anytime buttons
    if ($args['automaticAction'] ?? false) {
      return;
    }
    $player = Players::getActive();

    $args['canUseRuby'] = $player->countReserveResource(RUBY) > 0;

    // Anytime cards
    $listeningCards = Buildings::getReaction(
      [
        'type' => 'anytime',
        'method' => 'atAnytime',
        'action' => $action,
        'pId' => $player->getId(),
      ],
      false
    );

    // Reorganize animals
    if ($args['canGoToReorganize'] ?? true) {
      $listeningCards['childs'][] = ['action' => REORGANIZE, 'pId' => $player->getId(), 'desc' => '<REORGANIZE>'];
    }
    // Cook/exchange
    if ($args['canGoToExchange'] ?? true) {
      $listeningCards['childs'][] = ['action' => EXCHANGE, 'pId' => $player->getId(), 'desc' => '<EXCHANGE>'];
    }

    // Keep only doable actions
    $anytimeActions = [];
    foreach ($listeningCards['childs'] as $flow) {
      $tree = Engine::buildTree($flow);
      if ($tree->isDoable($player)) {
        $anytimeActions[] = [
          'flow' => $flow,
          'desc' => $flow['desc'] ?? $tree->getDescription(true),
          'optionalAction' => $tree->isOptional(),
          'independentAction' => $tree->isIndependent($player),
        ];
      }
    }
    $args['anytimeActions'] = $anytimeActions;
  }

  function takeIndependentMandatoryAnytimeActionIfAny()
  {
    return false;
    // $args = $this->gamestate->state()['args'];
    // if (!isset($args['anytimeActions']) || Globals::getAnytimeRecursion() > 0) {
    //   return false;
    // }
    //
    // $choice = null;
    // foreach ($args['anytimeActions'] as $choiceId => $action) {
    //   if ($action['independentAction'] && !$action['optionalAction']) {
    //     $choice = $choiceId;
    //     break;
    //   }
    // }
    //
    // if (is_null($choice)) {
    //   return false;
    // }
    //
    // Globals::incAnytimeRecursion();
    // $this->actAnytimeAction($choice, true);
    // Globals::setAnytimeRecursion(0);
  }

  function actAnytimeAction($choiceId, $auto = false)
  {
    $args = $this->gamestate->state()['args'];
    if (!isset($args['anytimeActions'][$choiceId])) {
      throw new \BgaVisibleSystemException('You can\'t take this anytime action');
    }

    $flow = $args['anytimeActions'][$choiceId]['flow'];
    if (!$auto) {
      Globals::incEngineChoices();
    }
    Engine::insertAtRoot($flow, false);
    Engine::proceed();
  }

  /**
   * Pass the argument of the action to the atomic action
   */
  function actTakeAtomicAction($args)
  {
    // throw new \feException(print_r($args));
    $action = $this->getCurrentAtomicAction();
    Actions::takeAction($action, $args, Engine::getNextUnresolved());
  }

  /**
   * To pass if the action is an optional one
   *
   */
  function actPassOptionalAction($auto = false)
  {
    if ($auto) {
      $this->gamestate->checkPossibleAction('actPassOptionalAction');
    } else {
      self::checkAction('actPassOptionalAction');
    }

    if (!Engine::getNextUnresolved()->isOptional()) {
      self::error(Engine::getNextUnresolved()->toArray());
      throw new \BgaVisibleSystemException('This action is not optional');
    }

    Engine::resolve(PASS);
    Engine::proceed();
  }

  /**
   * Pass the argument of the action to the atomic action
   */
  function stAtomicAction()
  {
    if ($this->takeIndependentMandatoryAnytimeActionIfAny()) {
      return;
    }

    $action = $this->getCurrentAtomicAction();
    Actions::stAction($action, Engine::getNextUnresolved());
  }

  /***
   * Ruby management
   */
  function actUseRuby($power)
  {
    self::checkAction('actUseRuby');
    Globals::incEngineChoices();

    $player = Players::getCurrent();
    if ($player->countReserveResource(RUBY) == 0) {
      throw new \BgaUserException(clienttranslate('You do not have a ruby to convert'));
    }

    $cost = [RUBY => $power == \TILE_CAVERN ? 2 : 1];
    if ($power == CATTLE) {
      $cost[FOOD] = 1;
    }

    $flow = [
      'type' => NODE_SEQ,
      'childs' => [
        [
          'action' => PAY,
          'args' => [
            'costs' => Utils::formatCost([RUBY => 1]),
            'nb' => 1,
            'source' => clienttranslate('ruby conversion'),
          ],
        ],
      ],
    ];

    if (in_array($power, RESOURCES)) {
      $flow['childs'][] = ['action' => GAIN, 'args' => [$power => 1]];
    } else {
      $flow['childs'][] = ['action' => PLACE_TILE, 'args' => ['tiles' => [$power]]];
    }

    Engine::insertAtRoot($flow, false);
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
}
