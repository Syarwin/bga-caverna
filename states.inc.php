<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * caverna implementation : © Timothée Pecatte <tim.pecatte@gmail.com>, Vincent Toper <vincent.toper@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * states.inc.php
 *
 * caverna game states description
 *
 */

$machinestates = [
  // The initial state. Please do not modify.
  ST_GAME_SETUP => [
    'name' => 'gameSetup',
    'description' => '',
    'type' => 'manager',
    'action' => 'stGameSetup',
    'transitions' => ['' => ST_START_GAME],
  ],

  ST_GENERIC_NEXT_PLAYER => [
    'name' => 'genericNextPlayer',
    'type' => 'game',
  ],

  ST_START_GAME => [
    'name' => 'startGame',
    'description' => '',
    'type' => 'game',
    'action' => 'stStartGame',
    'transitions' => [
      'noDraft' => ST_BEFORE_START_OF_TURN,
      'seed' => ST_LOAD_SEED,
    ],
  ],

  ST_LOAD_SEED => [
    'name' => 'loadSeed',
    'description' => clienttranslate('Please enter a valid seed to load the game'),
    'descriptionmyturn' => clienttranslate('Please enter a valid seed to load the game'),
    'type' => 'multipleactiveplayer',
    'possibleactions' => ['actLoadSeed'],
    'transitions' => ['start' => ST_BEFORE_START_OF_TURN],
  ],

  ST_BEFORE_START_OF_TURN => [
    'name' => 'beforeStartOfTurn',
    'description' => '',
    'type' => 'game',
    'action' => 'stBeforeStartOfTurn',
    'updateGameProgression' => true,
  ],

  ST_RESOLVE_STACK => [
    'name' => 'resolveStack',
    'type' => 'game',
    'action' => 'stResolveStack',
    'transitions' => [],
  ],

  ST_CONFIRM_TURN => [
    'name' => 'confirmTurn',
    'description' => clienttranslate('${actplayer} must confirm or restart their turn'),
    'descriptionmyturn' => clienttranslate('${you} must confirm or restart your turn'),
    'type' => 'activeplayer',
    'args' => 'argsConfirmTurn',
    'action' => 'stConfirmTurn',
    'possibleactions' => ['actConfirmTurn', 'actRestart', 'actUseRuby'],
  ],

  ST_CONFIRM_PARTIAL_TURN => [
    'name' => 'confirmPartialTurn',
    'description' => clienttranslate('${actplayer} must confirm the switch of player'),
    'descriptionmyturn' => clienttranslate(
      '${you} must confirm the switch of player. You will not be able to restart turn'
    ),
    'type' => 'activeplayer',
    'args' => 'argsConfirmTurn',
    // 'action' => 'stConfirmPartialTurn',
    'possibleactions' => ['actConfirmPartialTurn', 'actRestart', 'actUseRuby'],
  ],

  ST_RESOLVE_CHOICE => [
    'name' => 'resolveChoice',
    'description' => clienttranslate('${actplayer} must choose an action'),
    'descriptionmyturn' => clienttranslate('${you} must choose an action'),
    'type' => 'activeplayer',
    'args' => 'argsResolveChoice',
    'action' => 'stResolveChoice',
    'possibleactions' => ['actChooseAction', 'actRestart', 'actUseRuby'],
    'transitions' => [],
  ],

  ST_IMPOSSIBLE_MANDATORY_ACTION => [
    'name' => 'impossibleAction',
    'description' => clienttranslate(
      '${actplayer} can\'t take the mandatory action and must restart his turn or exchange/cook'
    ),
    'descriptionmyturn' => clienttranslate(
      '${you} can\'t take the mandatory action. Restart your turn or exchange/cook to make it possible'
    ),
    'type' => 'activeplayer',
    'args' => 'argsImpossibleAction',
    'possibleactions' => ['actRestart'],
  ],

  ST_PREPARATION => [
    'name' => 'preparation',
    'description' => '',
    'type' => 'game',
    'action' => 'stPreparation',
    'updateGameProgression' => true,
  ],

  ST_LABOR => [
    'name' => 'labor',
    'description' => '',
    'type' => 'game',
    'action' => 'stLabor',
    'transitions' => [
      'done' => ST_END_WORK_PHASE,
    ],
  ],

  ST_PLACE_DWARF => [
    'name' => 'placeDwarf',
    'description' => clienttranslate('${actplayer} must place a dwarf (${dwarfDesc})'),
    'descriptionmyturn' => clienttranslate('${you} must place a dwarf (${dwarfDesc})'),
    'descriptionskippable' => clienttranslate('${actplayer} may place a dwarf (${dwarfDesc})'),
    'descriptionmyturnskippable' => clienttranslate('${you} may place a dwarf (${dwarfDesc})'),
    'args' => 'argsAtomicAction',
    'type' => 'activeplayer',
    'action' => 'stAtomicAction',
    'possibleactions' => ['actPlaceDwarf', 'actPassOptionalAction', 'actRestart', 'actUseRuby'],
    'transitions' => [],
  ],

  ST_GAIN => [
    'name' => 'gainResources',
    'type' => 'game',
    'action' => 'stAtomicAction',
  ],

  ST_COLLECT => [
    'name' => 'collectResources',
    'type' => 'game',
    'action' => 'stAtomicAction',
  ],

  ST_RECEIVE => [
    'name' => 'receiveResources',
    'type' => 'game',
    'action' => 'stAtomicAction',
  ],

  ST_PLACE_FUTURE_MEEPLES => [
    'name' => 'placeFutureMeeples',
    'type' => 'game',
    'action' => 'stAtomicAction',
  ],

  ST_REAP => [
    'name' => 'reapCrops',
    'type' => 'game',
    'action' => 'stAtomicAction',
  ],

  ST_BLACKSMITH => [
    'name' => 'blacksmith',
    'description' => clienttranslate('${actplayer} must forge a weapon'),
    'descriptionmyturn' => clienttranslate('${you} must forge a weapon with a maximum strength of ${max}'),
    'args' => 'argsAtomicAction',
    'action' => 'stAtomicAction',
    'type' => 'activeplayer',
    'possibleactions' => ['actBlacksmith', 'actPassOptionalAction', 'actRestart', 'actUseRuby'],
  ],

  ST_BREED => [
    'name' => 'breed',
    'description' => clienttranslate('${actplayer} must breed all their animals'),
    'descriptionmyturn' => clienttranslate('${you} must breed all your animals'),
    'descriptionchoice' => clienttranslate('${actplayer} must breed ${max} animals'),
    'descriptionmyturnchoice' => clienttranslate('${you} must breed ${max} animals'),
    'args' => 'argsAtomicAction',
    'action' => 'stAtomicAction',
    'type' => 'activeplayer',
    'possibleactions' => ['actBreed', 'actPassOptionalAction', 'actRestart', 'actUseRuby'],
  ],

  ST_EXPEDITION => [
    'name' => 'expedition',
    'description' => clienttranslate('${actplayer} may take up to ${n} loot items of strength at most ${max}'),
    'descriptionmyturn' => clienttranslate('${you} may take up to ${n} loot items of strength at most ${max}'),
    'args' => 'argsAtomicAction',
    'action' => 'stAtomicAction',
    'type' => 'activeplayer',
    'possibleactions' => ['actExpedition', 'actPassOptionalAction', 'actRestart', 'actUseRuby'],
  ],

  ST_WEAPON_INCREASE => [
    'name' => 'weaponIncrease',
    'description' => clienttranslate('${actplayer} increases the weapons of all armed dwarf of 1'),
    'descriptionmyturn' => clienttranslate('${you} increases the weapons of all armed dwarf of 1'),
    'action' => 'stAtomicAction',
    'type' => 'activeplayer',
    'possibleactions' => ['actWeaponIncrease', 'actRestart', 'actUseRuby'],
  ],

  ST_PAY => [
    'name' => 'payResources',
    'description' => clienttranslate('${actplayer} must choose how to pay for ${source}'),
    'descriptionmyturn' => clienttranslate('${you} must choose how to pay for ${source}'),
    'descriptionauto' => clienttranslate('${actplayer} pays for ${source}'),
    'descriptionmyturnauto' => clienttranslate('${you} pay for ${source}'),
    'args' => 'argsAtomicAction',
    'action' => 'stAtomicAction',
    'type' => 'activeplayer',
    'possibleactions' => ['actPay', 'actPassOptionalAction', 'actRestart', 'actUseRuby'],
  ],

  ST_FIRSTPLAYER => [
    'name' => 'firstPlayer',
    'description' => '',
    'action' => 'stAtomicAction',
    'type' => 'game',
  ],

  ST_PLACE_TILE => [
    'name' => 'placeTile',
    'description' => clienttranslate('${actplayer} must place ${tiles}'),
    'descriptionmyturn' => clienttranslate('${you} must place ${tiles}'),
    'descriptionskippable' => clienttranslate('${actplayer} may place ${tiles}'),
    'descriptionmyturnskippable' => clienttranslate('${you} may place ${tiles}'),
    'args' => 'argsAtomicAction',
    'action' => 'stAtomicAction',
    'type' => 'activeplayer',
    'possibleactions' => ['actPlaceTile', 'actPassOptionalAction', 'actRestart', 'actUseRuby'],
  ],

  ST_FURNISH => [
    'name' => 'furnish',
    'description' => clienttranslate('${actplayer} must furnish its cavern'),
    'descriptionmyturn' => clienttranslate('${you} must furnish your cavern'),
    'descriptionskippable' => clienttranslate('${actplayer} may furnish its cavern'),
    'descriptionmyturnskippable' => clienttranslate('${you} may furnish your cavern'),
    'args' => 'argsAtomicAction',
    'action' => 'stAtomicAction',
    'type' => 'activeplayer',
    'possibleactions' => ['actFurnish', 'actPassOptionalAction', 'actRestart', 'actUseRuby'],
  ],

  ST_IMITATION => [
    'name' => 'imitate',
    'description' => clienttranslate('${actplayer} must choose the action space they want to copy'),
    'descriptionmyturn' => clienttranslate('${you} must choose the action space you want to copy'),
    'args' => 'argsAtomicAction',
    'action' => 'stAtomicAction',
    'type' => 'activeplayer',
    'possibleactions' => ['actImitate', 'actPassOptionalAction', 'actRestart', 'actUseRuby'],
  ],

  ST_SOW => [
    'name' => 'sow',
    'description' => clienttranslate('${actplayer} must sow their fields'),
    'descriptionmyturn' => clienttranslate('${you} must sow your field(s)'),
    'descriptionskippable' => clienttranslate('${actplayer} may sow their fields'),
    'descriptionmyturnskippable' => clienttranslate('${you} may sow your field(s)'),
    'args' => 'argsAtomicAction',
    'action' => 'stAtomicAction',
    'type' => 'activeplayer',
    'possibleactions' => ['actSow', 'actPassOptionalAction', 'actRestart', 'actUseRuby'],
  ],

  ST_STABLE => [
    'name' => 'stables',
    'description' => clienttranslate('${actplayer} must build stable(s)'),
    'descriptionmyturn' => clienttranslate('${you} must build up to ${max} stable(s)'),
    'descriptionskippable' => clienttranslate('${actplayer} may build stable(s)'),
    'descriptionmyturnskippable' => clienttranslate('${you} may build up to ${max} stable(s)'),
    'descriptionmyturnnomore' => clienttranslate(
      '${you} may construct up to ${max} stable(s) (quantity of stables in your reserve)'
    ),
    'args' => 'argsAtomicAction',
    'action' => 'stAtomicAction',
    'type' => 'activeplayer',
    'possibleactions' => ['actStables', 'actPassOptionalAction', 'actRestart', 'actUseRuby'],
  ],

  ST_REORGANIZE => [
    'name' => 'reorganize',
    'description' => clienttranslate(
      '${actplayer} must reorganize their animals inside their pastures, rooms and stables'
    ),
    'descriptionmyturn' => clienttranslate('You must reorganize your animals inside your pastures, rooms and stables'),
    'action' => 'stAtomicAction',
    'args' => 'argsAtomicAction',
    'type' => 'activeplayer',
    'possibleactions' => ['actReorganize', 'actRestart', 'actUseRuby'],
  ],

  ST_WISHCHILDREN => [
    'name' => 'wishForChildren',
    'description' => '',
    'descriptionmyturn' => '',
    'action' => 'stAtomicAction',
    'type' => 'game',
  ],

  ST_EXCHANGE => [
    'name' => 'exchange',
    'description' => clienttranslate('${actplayer} may exchange resources'),
    'descriptionmyturn' => clienttranslate('You may exchange resources'),
    'descriptionbread' => clienttranslate('${actplayer} must bake bread'),
    'descriptionmyturnbread' => clienttranslate('You must bake bread'),
    'descriptionbreadskippable' => clienttranslate('${actplayer} may bake bread'),
    'descriptionmyturnbreadskippable' => clienttranslate('You may bake bread'),
    'descriptionharvest' => clienttranslate('${actplayer} may exchange resources before feeding their family'),
    'descriptionmyturnharvest' => clienttranslate('You may exchange resources before feeding your family'),
    'descriptioncook' => clienttranslate('${actplayer} is cooking their animals'),
    'descriptionmyturncook' => clienttranslate('You are cooking your animals'),
    'args' => 'argsAtomicAction',
    'type' => 'activeplayer',
    'action' => 'stAtomicAction',
    'possibleactions' => ['actExchange', 'actPassOptionalAction', 'actRestart', 'actUseRuby'],
  ],

  ST_HARVEST_CHOICE => [
    'name' => 'harvestChoice',
    'description' => clienttranslate('${actplayer} must choose which phase will be done for this harvest'),
    'descriptionmyturn' => clienttranslate('You must choose which phase will be done for this harvest'),
    'action' => 'stAtomicAction',
    'args' => 'argsAtomicAction',
    'type' => 'activeplayer',
    'possibleactions' => ['actHarvestChoice', 'actRestart', 'actUseRuby'],
  ],

  ST_ACTIVATE_BUILDING => [
    'name' => 'activateCard',
    'description' => '',
    'type' => 'game',
    'action' => 'stAtomicAction',
    'transitions' => [],
  ],

  ST_SPECIAL_EFFECT => [
    'name' => 'specialEffect',
    'description' => '',
    'descriptionmyturn' => '',
    'action' => 'stAtomicAction',
    'args' => 'argsAtomicAction',
    'type' => 'activeplayer',
    'possibleactions' => ['actPassOptionalAction', 'actRestart', 'actUseRuby'],
  ],

  ST_END_WORK_PHASE => [
    'name' => 'endWorkPhase',
    'description' => '',
    'type' => 'game',
    'action' => 'stEndWorkPhase',
  ],

  ST_START_HARVEST => [
    'name' => 'startHarvest',
    'description' => '',
    'type' => 'game',
    'action' => 'stStartHarvest',
    'transitions' => [
      'end' => ST_END_OF_TURN,
    ],
  ],

  ST_HARVEST_FIELD => [
    'name' => 'harvestCrop',
    'description' => '',
    'type' => 'game',
    'action' => 'stHarvestFieldPhase',
  ],

  ST_HARVEST_FEED => [
    'name' => 'harvestFeed',
    'description' => '',
    'type' => 'game',
    'action' => 'stHarvestFeed',
  ],

  ST_HARVEST_BREED => [
    'name' => 'harvestBreed',
    'description' => '',
    'type' => 'game',
    'action' => 'stHarvestBreed',
  ],

  ST_PRE_END_OF_TURN => [
    'name' => 'preEndOfTurn',
    'description' => '',
    'type' => 'game',
    'action' => 'stPreEndOfTurn',
    'transitions' => [
      'harvest' => ST_START_HARVEST,
      'end' => ST_END_OF_TURN,
    ],
  ],

  ST_END_OF_TURN => [
    'name' => 'endOfTurn',
    'description' => '',
    'type' => 'game',
    'action' => 'stEndOfTurn',
    'transitions' => [
      'newTurn' => ST_BEFORE_START_OF_TURN,
      'end' => ST_PRE_END_OF_GAME,
    ],
  ],

  ST_PRE_END_OF_GAME => [
    'name' => 'preEndOfGame',
    'type' => 'game',
    'action' => 'stPreEndOfGame',
    'transitions' => ['' => ST_END_GAME],
  ],

  // Final state.
  // Please do not modify (and do not overload action/args methods).
  ST_END_GAME => [
    'name' => 'gameEnd',
    'description' => clienttranslate('End of game'),
    'type' => 'manager',
    'action' => 'stGameEnd',
    'args' => 'argGameEnd',
  ],
];
