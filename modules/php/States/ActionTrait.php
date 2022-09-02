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

    $player = Players::getCurrent();
    if ($player->countReserveResource(RUBY) == 0) {
      throw new \BgaUserException(clienttranslate('You do not have a ruby to convert'));
    }

    $powers = [
      WOOD => [
        'type' => NODE_SEQ,
        'childs' => [
          [
            'action' => PAY,
            'args' => ['costs' => Utils::formatCost([RUBY => 1]), 'nb' => 1, 'source' => clienttranslate('conversion')],
          ],
          ['action' => GAIN, 'args' => [WOOD => 1]],
        ],
      ],
      STONE => [
        'type' => NODE_SEQ,
        'childs' => [
          ['action' => PAY, 'args' => ['costs' => Utils::formatCost([RUBY => 1]), 'nb' => 1]],
          ['action' => GAIN, 'args' => [STONE => 1]],
        ],
      ],
      ORE => [
        'type' => NODE_SEQ,
        'childs' => [
          ['action' => PAY, 'args' => ['costs' => Utils::formatCost([RUBY => 1]), 'nb' => 1]],
          ['action' => GAIN, 'args' => [ORE => 1]],
        ],
      ],
      GRAIN => [
        'type' => NODE_SEQ,
        'childs' => [
          ['action' => PAY, 'args' => ['costs' => Utils::formatCost([RUBY => 1]), 'nb' => 1]],
          ['action' => GAIN, 'args' => [GRAIN => 1]],
        ],
      ],
      VEGETABLE => [
        'type' => NODE_SEQ,
        'childs' => [
          ['action' => PAY, 'args' => ['costs' => Utils::formatCost([RUBY => 1]), 'nb' => 1]],
          ['action' => GAIN, 'args' => [VEGETABLE => 1]],
        ],
      ],
      SHEEP => [
        'type' => NODE_SEQ,
        'childs' => [
          ['action' => PAY, 'args' => ['costs' => Utils::formatCost([RUBY => 1]), 'nb' => 1]],
          ['action' => GAIN, 'args' => [SHEEP => 1]],
        ],
      ],
      PIG => [
        'type' => NODE_SEQ,
        'childs' => [
          ['action' => PAY, 'args' => ['costs' => Utils::formatCost([RUBY => 1]), 'nb' => 1]],
          ['action' => GAIN, 'args' => [PIG => 1]],
        ],
      ],
      DOG => [
        'type' => NODE_SEQ,
        'childs' => [
          ['action' => PAY, 'args' => ['costs' => Utils::formatCost([RUBY => 1]), 'nb' => 1]],
          ['action' => GAIN, 'args' => [DOG => 1]],
        ],
      ],
      DONKEY => [
        'type' => NODE_SEQ,
        'childs' => [
          ['action' => PAY, 'args' => ['costs' => Utils::formatCost([RUBY => 1]), 'nb' => 1]],
          ['action' => GAIN, 'args' => [DONKEY => 1]],
        ],
      ],
      CATTLE => [
        'type' => NODE_SEQ,
        'childs' => [
          ['action' => PAY, 'args' => ['costs' => Utils::formatCost([RUBY => 1, FOOD => 1]), 'nb' => 1]],
          ['action' => GAIN, 'args' => [\CATTLE => 1]],
        ],
      ],
      GOLD => [
        'type' => NODE_SEQ,
        'childs' => [
          ['action' => PAY, 'args' => ['costs' => Utils::formatCost([RUBY => 1]), 'nb' => 1]],
          ['action' => GAIN, 'args' => [GOLD => 1]],
        ],
      ],
      PLACE_TILE => [
        'type' => NODE_SEQ,
        'childs' => [
          ['action' => PAY, 'args' => ['costs' => Utils::formatCost([RUBY => 1]), 'nb' => 1]],
          ['action' => PLACE_TILE, 'args' => ['tiles' => [TILE_MEADOW, TILE_FIELD, TILE_TUNNEL]]],
        ],
      ],
      'place_cavern' => [
        'type' => NODE_SEQ,
        'childs' => [
          ['action' => PAY, 'args' => ['costs' => Utils::formatCost([RUBY => 2]), 'nb' => 1]],
          ['action' => PLACE_TILE, 'args' => ['tiles' => [TILE_CAVERN]]],
        ],
      ],
    ];
    if (!isset($powers[$power])) {
      throw new \BgaVisibleSystemException('Power not defined. Should not happen');
    }

    Engine::insertAtRoot($powers[$power], false);
    Engine::proceed();
  }
}
