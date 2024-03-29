<?php

namespace CAV;

use CAV\Core\Globals;
use CAV\Managers\Players;
use CAV\Managers\Meeples;
use CAV\Managers\Fences;
use CAV\Managers\Actions;
use CAV\Managers\Buildings;
use CAV\Managers\ActionCards;
use CAV\Core\Engine;
use CAV\Core\Game;
use CAV\Models\PlayerBoard;
use CAV\Core\Notifications;
use CAV\Helpers\Utils;

trait DebugTrait
{
  function tp()
  {
    // self::reloadPlayersBasicInfos();
    $player = Players::getCurrent();
    $costs = $player->getHarvestCost();
    Buildings::applyEffects($player, 'ComputeHarvestCosts', $costs);
    var_dump($costs);
    // var_dump($player->getPlayedBuildings());
    //    Buildings::setupNewGame(Players::getAll(), []);
  }

  function dv()
  {
    // throw new feException(print_r(Meeples::getRoomsToBuild(2305526)));
    // throw new feException(print_r(ActionCards::getInLocation('turn%', 1)));
    ActionCards::accumulate();
    // Meeples::createResource('wood', 'reserve', self::getCurrentPlayerId(), 0, 0, 5);
    // Engine::insertAsChild(['action' => 'toto']);
    // throw new feException(Fences::hasAvailable(self::getCurrentPlayerId()));
    // $player = Players::getActive();
    //$this->actFence([['x' => 0, 'y' => 1], ['x' => 1, 'y' => 0], ['x' => 1, 'y' => 2], ['x' => 2, 'y' => 1]]);
    // Meeples::useResource($this->getCurrentPlayerId(), 'wood', 4);
    //$this->actTakeAtomicAction([[['x' => 3, 'y' => 1], ['x' => 1, 'y' => 1]]]);
    // $this->actTakeAtomicAction([[['x' => 5, 'y' => 1]]]);
    //$this->gamestate->jumpToState(ST_HARVEST_FIELD);
    //CAV\Managers\Scores::update(true);
    // $this->saveSeed();
    // $seed = Globals::getGameSeed();
    // var_dump($seed);

    // $sql = "SELECT * FROM `global_variables` WHERE `name` = 'engine' AND `value` LIKE '%\"trigger\": 3%' ";
    // $row = self::getUniqueValueFromDB($sql);
    // var_dump($row);
  }

  function vt()
  {
    // Engine::insertAsChild([
    //   'action' => PLACE_DWARF,
    // ]);
    // Engine::resolveAction();
    // Engine::proceed();

    // $this->actTakeAtomicAction([null, 25]);
    // $this->stInitHarvestFeedingPhase();
    // Globals::setHarvestCost(1);
    // $this->checkBuildingListeners('BeforeHarvest', ST_START_HARVEST);
    // Engine::insertAtRoot(
    //   [
    //     'action' => PLACE_TILE,
    //     'args' => ['tiles' => [\TILE_FIELD]],
    //   ],
    //   false
    // );
    // Engine::insertAtRoot(['action' => FURNISH], false);
    // Engine::proceed();
    // ActionCards::accumulate();
    $this->actRubyChoice(['ActionLogging']);
  }
  public function tv()
  {
    // $this->actTakeAtomicAction([[SHEEP, CATTLE]]);
    // stPreEndOfTurn()
    $this->actTakeAtomicAction([BREED]);
  }

  public function dd()
  {
    // throw new \feException(print_r(Globals::getEngine()));
    // Buildings::get('A71_ClearingSpade')->moveCrop(149, 165);
    $this->actTakeAtomicAction([[['id' => 'B68_Beanfield', 'crop' => VEGETABLE]]]);
  }

  public function nt()
  {
    $player = Players::getCurrent();
    Engine::insertAtRoot([
      'action' => PLACE_DWARF,
      'pId' => $player->getId(),
    ]);
    Engine::save();
    Engine::proceed();
  }

  function addResource($type, $qty = 1)
  {
    if (!in_array($type, RESOURCES)) {
      throw new BgaVisibleSystemException("Didn't recognized the resource : " . $type);
    }

    $player = Players::getCurrent();
    $meeples = $player->createResourceInReserve($type, $qty);
    Notifications::gainResources($player, $meeples);
    Engine::proceed();
  }

  function infResources()
  {
    $player = Players::getCurrent();
    $meeples = [];
    foreach ([WOOD, STONE, ORE, RUBY, GOLD] as $res) {
      $meeples = array_merge($meeples, $player->createResourceInReserve($res, 8));
    }
    Notifications::gainResources($player, $meeples);
    Engine::proceed();
  }

  function allVisible()
  {
    $sql = "UPDATE `cards` set `card_state` = 1 where `card_location` like 'turn%'";
    self::DbQuery($sql);
  }

  function playCardAux($cardId, $doAction = true)
  {
    $player = Players::getCurrent();
    $pId = $player->getId();

    $sql = "SELECT * FROM cards WHERE card_id = '$cardId' LIMIT 1";
    $card = self::getUniqueValueFromDB($sql);

    if (is_null($card)) {
      $sql = "UPDATE cards set card_id = '$cardId' where player_id = $pId AND `card_location` <> 'inPlay' LIMIT 1";
    } else {
      $sql = "UPDATE cards set player_id = $pId where card_id = '$cardId'";
    }
    self::DbQuery($sql);

    if ($doAction) {
      $this->actTakeAtomicAction([$cardId]);
    }
  }

  function playCard($cardId)
  {
    self::playCardAux($cardId, true);
  }

  function addCard($cardId)
  {
    self::playCardAux($cardId, false);
    $sql = "UPDATE cards set card_location = 'inPlay' where card_id = '$cardId'";
    self::DbQuery($sql);
  }

  function drawCard($cardId)
  {
    self::playCardAux($cardId, false);
    $sql = "UPDATE cards set card_location = 'hand' where card_id = '$cardId'";
    self::DbQuery($sql);
  }

  function engSetup()
  {
    $pId = Players::getAll()->getIds()[0];

    Engine::setup([
      'childs' => [
        [
          'state' => ST_PLACE_DWARF,
          'pId' => $pId,
          'mandatory' => true,
        ],
      ],
    ]);
  }

  function engDisplay()
  {
    var_dump(Globals::getEngine());
  }

  function engProceed()
  {
    Engine::proceed();
  }

  /*
   * loadBug: in studio, type loadBug(20762) into the table chat to load a bug report from production
   * client side JavaScript will fetch each URL below in sequence, then refresh the page
   */
  public function loadBug($reportId)
  {
    $db = explode('_', self::getUniqueValueFromDB("SELECT SUBSTRING_INDEX(DATABASE(), '_', -2)"));
    $game = $db[0];
    $tableId = $db[1];
    self::notifyAllPlayers(
      'loadBug',
      "Trying to load <a href='https://boardgamearena.com/bug?id=$reportId' target='_blank'>bug report $reportId</a>",
      [
        'urls' => [
          // Emulates "load bug report" in control panel
          "https://studio.boardgamearena.com/admin/studio/getSavedGameStateFromProduction.html?game=$game&report_id=$reportId&table_id=$tableId",

          // Emulates "load 1" at this table
          "https://studio.boardgamearena.com/table/table/loadSaveState.html?table=$tableId&state=1",

          // Calls the function below to update SQL
          "https://studio.boardgamearena.com/1/$game/$game/loadBugSQL.html?table=$tableId&report_id=$reportId",

          // Emulates "clear PHP cache" in control panel
          // Needed at the end because BGA is caching player info
          "https://studio.boardgamearena.com/admin/studio/clearGameserverPhpCache.html?game=$game",
        ],
      ]
    );
  }

  /*
   * loadBugSQL: in studio, this is one of the URLs triggered by loadBug() above
   */
  public function loadBugSQL($reportId)
  {
    $studioPlayer = self::getCurrentPlayerId();
    $players = self::getObjectListFromDb('SELECT player_id FROM player', true);

    // Change for your game
    // We are setting the current state to match the start of a player's turn if it's already game over
    $sql = ['UPDATE global SET global_value=2 WHERE global_id=1 AND global_value=99'];
    $sql[] = 'ALTER TABLE `gamelog` ADD `cancel` TINYINT(1) NOT NULL DEFAULT 0;';
    $map = [];
    foreach ($players as $pId) {
      $map[(int) $pId] = (int) $studioPlayer;

      // All games can keep this SQL
      $sql[] = "UPDATE player SET player_id=$studioPlayer WHERE player_id=$pId";
      $sql[] = "UPDATE global SET global_value=$studioPlayer WHERE global_value=$pId";
      $sql[] = "UPDATE stats SET stats_player_id=$studioPlayer WHERE stats_player_id=$pId";

      // Add game-specific SQL update the tables for your game
      $sql[] = "UPDATE meeples SET player_id=$studioPlayer WHERE player_id=$pId";
      $sql[] = "UPDATE tiles SET player_id=$studioPlayer WHERE player_id=$pId";
      $sql[] = "UPDATE buildings SET player_id=$studioPlayer WHERE player_id=$pId";
      $sql[] = "UPDATE user_preferences SET player_id=$studioPlayer WHERE player_id=$pId";

      // This could be improved, it assumes you had sequential studio accounts before loading
      // e.g., quietmint0, quietmint1, quietmint2, etc. are at the table
      $studioPlayer++;
    }
    $msg =
      "<b>Loaded <a href='https://boardgamearena.com/bug?id=$reportId' target='_blank'>bug report $reportId</a></b><hr><ul><li>" .
      implode(';</li><li>', $sql) .
      ';</li></ul>';
    self::warn($msg);
    self::notifyAllPlayers('message', $msg, []);

    foreach ($sql as $q) {
      self::DbQuery($q);
    }

    /******************
     *** Fix Globals ***
     ******************/

    // Turn orders
    $turnOrders = Globals::getCustomTurnOrders();
    foreach ($turnOrders as $key => &$order) {
      $t = [];
      foreach ($order['order'] as $pId) {
        $t[] = $map[$pId];
      }
      $order['order'] = $t;
    }
    Globals::setCustomTurnOrders($turnOrders);

    // Engine
    $engine = Globals::getEngine();
    self::loadDebugUpdateEngine($engine, $map);
    Globals::setEngine($engine);

    // Skipped players
    $skippedPlayers = Globals::getSkippedPlayers();
    $t = [];
    foreach ($skippedPlayers as $pId) {
      $t[] = $map[$pId];
    }
    Globals::setSkippedPlayers($t);

    // First player
    $fp = Globals::getFirstPlayer();
    Globals::setFirstPlayer($map[$fp]);

    self::reloadPlayersBasicInfos();
  }

  function loadDebugUpdateEngine(&$node, $map)
  {
    if (isset($node['pId'])) {
      $node['pId'] = $map[(int) $node['pId']];
    }

    if (isset($node['childs'])) {
      foreach ($node['childs'] as &$child) {
        self::loadDebugUpdateEngine($child, $map);
      }
    }
  }

  /********************************
   ********* COMBO CHECKER *********
   ********************************/
  public function checkCombos()
  {
    $this->gamestate->jumpToState(\ST_CHECK_COMBOS);
  }

  public function getArgsCheckCombos($methodName)
  {
    // Load list of cards
    include dirname(__FILE__) . '/Cards/list.inc.php';
    $cards = [];
    foreach ($cardIds as $cId) {
      $card = Buildings::getCardInstance($cId);
      if (\method_exists($card, 'onPlayer' . $methodName)) {
        $cards[$cId] = $card;
      }
    }

    // Compute a specific ordering if needed
    $order = [];
    $edges = [];
    $orderName = 'order' . $methodName;
    foreach ($cards as $cId => $card) {
      if (\method_exists($card, $orderName)) {
        foreach ($card->$orderName() as $constraint) {
          $cId2 = $constraint[1];
          $op = $constraint[0];

          if (isset($order[$cId][$cId2]) && $order[$cId][$cId2] != $op) {
            throw new \feException('Incompatible ordering on following cards :' . $cId . ' ' . $cId2);
          }
          $order[$cId][$cId2] = $op;

          // Add the symmetric constraint
          $symOp = $op == '<' ? '>' : '<';
          if (isset($order[$cId2][$cId]) && $order[$cId2][$cId] != $symOp) {
            throw new \feException('Incompatible ordering on following cards :' . $cId . ' ' . $cId2);
          }
          $order[$cId2][$cId] = $symOp;

          // Add the edge
          $edges[] = [$op == '<' ? $cId : $cId2, $op == '<' ? $cId2 : $cId];
        }
      }
    }
    $nodes = array_keys($cards);
    $topoOrder = Utils::topological_sort($nodes, $edges);
    // Check if compute ordering respect every constaint
    if (true) {
      for ($i = 0; $i < count($cards); $i++) {
        for ($j = $i + 1; $j < count($cards); $j++) {
          $cId = $topoOrder[$i];
          $cId2 = $topoOrder[$j];
          if (isset($order[$cId][$cId2]) && $order[$cId][$cId2] != '<') {
            throw new \feException('Incompatible ordering after closure on following cards :' . $cId . ' ' . $cId2);
          }
        }
      }
    }

    $orderedCards = [];
    foreach ($topoOrder as $cId) {
      $orderedCards[] = $cards[$cId];
    }

    return [
      'cards' => $orderedCards,
      'order' => $order,
    ];
  }

  public function argsCheckCombos()
  {
    return [
      'construct' => $this->getArgsCheckCombos('ComputeCostsConstruct'),
      'renovate' => $this->getArgsCheckCombos('ComputeCostsRenovation'),
    ];
  }
}
