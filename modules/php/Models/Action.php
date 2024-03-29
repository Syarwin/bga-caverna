<?php
namespace CAV\Models;
use CAV\Core\Engine;
use CAV\Core\Game;
use CAV\Core\Globals;
use CAV\Managers\Buildings;
use CAV\Managers\Players;
use CAV\Managers\Dwarfs;

/*
 * Action: base class to handle atomic action
 */
class Action
{
  protected $ctx = null; // Contain ctx information : current node of flow tree
  protected $description = '';
  public function __construct($ctx)
  {
    $this->ctx = $ctx;
  }

  public function isDoable($player, $ignoreResources = false)
  {
    return true;
  }

  public function isOptional()
  {
    return false;
  }

  public function isIndependent($player = null)
  {
    return false;
  }

  public function isAutomatic($player = null)
  {
    return false;
  }

  public function getDescription($ignoreResources = false)
  {
    return $this->description;
  }

  public function getPlayer()
  {
    $pId = $this->ctx->getPId() ?? Players::getActiveId();
    return Players::get($pId);
  }

  public function getState()
  {
    return null;
  }

  public function isHarvest()
  {
    return Globals::isHarvest();
  }

  /**
   * Syntaxic sugar
   */
  public function resolveAction($args = [], $automatic = null)
  {
    $player = Players::getActive();
    $args['automatic'] = $automatic ?? $this->isAutomatic($player);
    Engine::resolveAction($args);
    Engine::proceed();
  }

  public static function checkAction($action, $byPassActiveCheck = false)
  {
    if ($byPassActiveCheck) {
      Game::get()->gamestate->checkPossibleAction($action);
    } else {
      Game::get()->checkAction($action);
    }
  }

  public function getCtxArgs()
  {
    if ($this->ctx == null) {
      return [];
    }
    return $this->ctx->getArgs() ?? [];
  }

  public function getDwarf()
  {
    $dwarfId = $this->ctx->getDwarfId();
    return is_null($dwarfId) ? null : Dwarfs::get($dwarfId);
  }

  public function getClassName()
  {
    $classname = get_class($this);
    if ($pos = strrpos($classname, '\\')) {
      return substr($classname, $pos + 1);
    }
    return $classname;
  }

  /*
  public function checkBeforeEffects($player, $args = [])
  {
    $args = array_merge($args, ['ctx' => $this->ctx]);
    return !Buildings::applyEffects($player, 'Before' . $this->getClassName(), $args, 'or');
  }
*/

  protected function checkListeners($method, $player, $args = [])
  {
    $event = array_merge(
      [
        'pId' => $player->getId(),
        'type' => 'action',
        'action' => $this->getClassName(),
        'method' => $method,
      ],
      $args
    );

    $reaction = Buildings::getReaction($event);
    if (!is_null($reaction)) {
      Engine::insertAsChild($reaction);
    }
  }

  public function checkAfterListeners($player, $args = [], $duringActionListener = true)
  {
    if ($duringActionListener) {
      $this->checkListeners($this->getClassName(), $player, $args);
    }
    $this->checkListeners('ImmediatelyAfter' . $this->getClassName(), $player, $args);
    $this->checkListeners('After' . $this->getClassName(), $player, $args);
  }

  public function checkModifiers($method, &$data, $name, $player, $args = [])
  {
    $args[$name] = $data;
    $args['actionCardId'] = $this->ctx != null ? $this->ctx->getCardId() : null;
    Buildings::applyEffects($player, $method, $args);
    $data = $args[$name];
  }

  public function checkCostModifiers(&$costs, $player, $args = [])
  {
    $this->checkModifiers('computeCosts' . $this->getClassName(), $costs, 'costs', $player, $args);
  }

  public function checkArgsModifiers(&$actionArgs, $player, $args = [])
  {
    $this->checkModifiers('computeArgs' . $this->getClassName(), $actionArgs, 'actionArgs', $player, $args);
  }
}
