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
    if(!isset($args['source']) && !is_null($sourceId)){
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
      $listeningCards['childs'][] = ['action' => EXCHANGE, 'pId' => $player->getId(), 'desc' => '<COOK>'];
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
    $args = $this->gamestate->state()['args'];
    if (!isset($args['anytimeActions']) || Globals::getAnytimeRecursion() > 0) {
      return false;
    }

    $choice = null;
    foreach ($args['anytimeActions'] as $choiceId => $action) {
      if ($action['independentAction'] && !$action['optionalAction']) {
        $choice = $choiceId;
        break;
      }
    }

    if (is_null($choice)) {
      return false;
    }


    Globals::incAnytimeRecursion();
    $this->actAnytimeAction($choice, true);
    Globals::setAnytimeRecursion(0);
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
    if($this->takeIndependentMandatoryAnytimeActionIfAny()){
      return;
    }

    $action = $this->getCurrentAtomicAction();
    Actions::stAction($action, Engine::getNextUnresolved());
  }
}
