<?php
namespace CAV\Managers;
use CAV\Core\Game;

/* Class to manage all the cards for Agricola */

class Actions
{
  static $classes = [
    ACTIVATE_BUILDING => 'ActivateBuilding',
    BLACKSMITH => 'Blacksmith',
    COLLECT => 'Collect',
    PLACE_TILE => 'PlaceTile',
    EXCHANGE => 'Exchange',
    EXPEDITION => 'Expedition',
    FIRSTPLAYER => 'FirstPlayer',
    FURNISH => 'Furnish',
    GAIN => 'Gain',
    IMITATE => 'Imitate',
    PAY => 'Pay',
    PLACE_DWARF => 'PlaceDwarf',
    PLACE_FUTURE_MEEPLES => 'PlaceFutureMeeples',
    REAP => 'Reap',
    RECEIVE => 'Receive',
    REORGANIZE => 'Reorganize',
    SOW => 'Sow',
    STABLES => 'Stables',
    SPECIAL_EFFECT => 'SpecialEffect',
    WISHCHILDREN => 'WishChildren',
    WEAPON_INCREASE => 'WeaponIncrease',
    BREED => 'Breed',
    HARVEST_CHOICE => 'HarvestChoice',
  ];

  public static function get($actionId, $ctx = null)
  {
    if (!\array_key_exists($actionId, self::$classes)) {
      throw new \BgaVisibleSystemException('Trying to get an atomic action not defined in Actions.php : ' . $actionId);
    }
    $name = '\CAV\Actions\\' . self::$classes[$actionId];
    return new $name($ctx);
  }

  public static function getActionOfState($stateId, $throwErrorIfNone = true)
  {
    foreach (array_keys(self::$classes) as $actionId) {
      if (self::getState($actionId, null) == $stateId) {
        return $actionId;
      }
    }

    if ($throwErrorIfNone) {
      throw new \BgaVisibleSystemException('Trying to fetch args of a non-declared atomic action in state ' . $stateId);
    } else {
      return null;
    }
  }

  public static function isDoable($actionId, $ctx, $player, $ignoreResources = false)
  {
    $res = self::get($actionId, $ctx)->isDoable($player, $ignoreResources);
    // Cards that bypass isDoable (eg Paper Maker)
    $args = [
      'action' => $actionId,
      'ignoreResources' => $ignoreResources,
      'isDoable' => $res,
      'ctx' => $ctx,
    ];
    Buildings::applyEffects($player, 'isDoable', $args);
    return $args['isDoable'];
  }

  public static function getErrorMessage($actionId)
  {
    $actionId = ucfirst(mb_strtolower($actionId));
    $msg = sprintf(
      Game::get()::translate(
        'Attempting to take an action (%s) that is not possible. Either another card erroneously flagged this action as possible, or this action was possible until another card interfered.'
      ),
      $actionId
    );
    return $msg;
  }

  public static function getState($actionId, $ctx)
  {
    return self::get($actionId, $ctx)->getState();
  }

  public static function getArgs($actionId, $ctx)
  {
    $action = self::get($actionId, $ctx);
    $methodName = 'args' . self::$classes[$actionId];
    return array_merge($action->$methodName(), ['optionalAction' => $ctx->isOptional()]);
  }

  public static function takeAction($actionId, $args, $ctx)
  {
    $player = Players::getActive();
    if (!self::isDoable($actionId, $ctx, $player)) {
      throw new \BgaUserException(self::getErrorMessage($actionId));
    }

    $action = self::get($actionId, $ctx);
    $methodName = 'act' . self::$classes[$actionId];
    $action->$methodName(...$args);
  }

  public static function stAction($actionId, $ctx)
  {
    $player = Players::getActive();
    if (!self::isDoable($actionId, $ctx, $player)) {
      if (!$ctx->isOptional()) {
        if (self::isDoable($actionId, $ctx, $player, true)) {
          Game::get()->gamestate->jumpToState(ST_IMPOSSIBLE_MANDATORY_ACTION);
          return;
        } else {
          throw new \BgaUserException(self::getErrorMessage($actionId) . ". Active player : " . $player->getId(). ". Debug context : ". \var_export($ctx->toArray(), true));
        }
      } else {
        // Auto pass if optional and not doable
        Game::get()->actPassOptionalAction(true);
        return;
      }
    }

    $action = self::get($actionId, $ctx);
    $methodName = 'st' . self::$classes[$actionId];
    if (\method_exists($action, $methodName)) {
      $action->$methodName();
    }
  }
}
