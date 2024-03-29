<?php
namespace CAV\Core;

use CAV\Core\Game;
/*
 * Globals
 */
class Globals extends \CAV\Helpers\DB_Manager
{
  protected static $initialized = false;
  protected static $variables = [
    'engine' => 'obj', // DO NOT MODIFY, USED IN ENGINE MODULE
    'engineChoices' => 'int', // DO NOT MODIFY, USED IN ENGINE MODULE
    'callbackEngineResolved' => 'obj', // DO NOT MODIFY, USED IN ENGINE MODULE
    'anytimeRecursion' => 'int', // DO NOT MODIFY, USED IN ENGINE MODULE

    'customTurnOrders' => 'obj', // DO NOT MODIFY, USED FOR CUSTOM TURN ORDER FEATURE

    'harvest' => 'bool',
    'skippedPlayers' => 'obj',
    'harvestCost' => 'int',
    'exchangeFlags' => 'obj',
    'revealStartHarvest' => 'bool',
    'harvestChoice' => 'obj',

    'rubyChoice' => 'obj',
    'gameSeed' => 'str',

    // Game options
    'solo' => 'bool',
    'beginner' => 'bool',
    'liveScoring' => 'bool',
    'turn' => 'int',
    'lastRevealed' => 'str',
    'firstPlayer' => 'int',

    'passHarvest' => 'obj',
    'skipHarvest' => 'obj',
    'workPhase' => 'bool',

    'breed' => 'obj', // for management of G_Quarry
  ];

  protected static $table = 'global_variables';
  protected static $primary = 'name';
  protected static function cast($row)
  {
    $val = json_decode(\stripslashes($row['value']), true);
    return self::$variables[$row['name']] == 'int' ? ((int) $val) : $val;
  }

  /*
   * Fetch all existings variables from DB
   */
  protected static $data = [];
  public static function fetch()
  {
    // Turn of LOG to avoid infinite loop (Globals::isLogging() calling itself for fetching)
    $tmp = self::$log;
    self::$log = false;

    foreach (
      self::DB()
        ->select(['value', 'name'])
        ->get(false)
      as $name => $variable
    ) {
      if (\array_key_exists($name, self::$variables)) {
        self::$data[$name] = $variable;
      }
    }
    self::$initialized = true;
    self::$log = $tmp;
  }

  /*
   * Create and store a global variable declared in this file but not present in DB yet
   *  (only happens when adding globals while a game is running)
   */
  public static function create($name)
  {
    if (!\array_key_exists($name, self::$variables)) {
      return;
    }

    $default = [
      'int' => 0,
      'obj' => [],
      'bool' => false,
      'str' => '',
    ];
    $val = $default[self::$variables[$name]];
    self::DB()->insert(
      [
        'name' => $name,
        'value' => \json_encode($val),
      ],
      true
    );
    self::$data[$name] = $val;
  }

  public static function init()
  {
    foreach (self::$variables as $name => $type) {
      self::create($name);
    }
  }

  /*
   * Magic method that intercept not defined static method and do the appropriate stuff
   */
  public static function __callStatic($method, $args)
  {
    if (!self::$initialized) {
      self::fetch();
    }

    if (preg_match('/^([gs]et|inc|is)([A-Z])(.*)$/', $method, $match)) {
      // Sanity check : does the name correspond to a declared variable ?
      $name = mb_strtolower($match[2]) . $match[3];
      if (!\array_key_exists($name, self::$variables)) {
        throw new \InvalidArgumentException("Property {$name} doesn't exist");
      }

      // Create in DB if don't exist yet
      if (!\array_key_exists($name, self::$data)) {
        self::create($name);
      }

      if ($match[1] == 'get') {
        // Basic getters
        return self::$data[$name];
      } elseif ($match[1] == 'is') {
        // Boolean getter
        if (self::$variables[$name] != 'bool') {
          throw new \InvalidArgumentException("Property {$name} is not of type bool");
        }
        return (bool) self::$data[$name];
      } elseif ($match[1] == 'set') {
        // Setters in DB and update cache
        $value = $args[0];
        if (self::$variables[$name] == 'int') {
          $value = (int) $value;
        }
        if (self::$variables[$name] == 'bool') {
          $value = (bool) $value;
        }

        self::$data[$name] = $value;
        self::DB()->update(['value' => \addslashes(\json_encode($value))], $name);
        return $value;
      } elseif ($match[1] == 'inc') {
        if (self::$variables[$name] != 'int') {
          throw new \InvalidArgumentException("Trying to increase {$name} which is not an int");
        }

        $getter = 'get' . $match[2] . $match[3];
        $setter = 'set' . $match[2] . $match[3];
        return self::$setter(self::$getter() + (empty($args) ? 1 : $args[0]));
      }
    }
    return undefined;
  }

  /*
   * Setup new game
   */
  public static function setupNewGame($players, $options)
  {
    self::init();

    self::setSolo(count($players) == 1);
    self::setBeginner($options[OPTION_COMPETITIVE_LEVEL] == OPTION_COMPETITIVE_BEGINNER);
    self::setLiveScoring($options[OPTION_SCORING] == OPTION_SCORING_ENABLED);
    self::setRevealStartHarvest(($options[OPTION_REVEAL_HARVEST] ?? null) == \OPTION_REVEAL_START);
    self::setTurn(0);
    self::setFirstPlayer(Game::get()->getNextPlayerTable()[0]);
    self::setHarvestCost(2);
  }
}
