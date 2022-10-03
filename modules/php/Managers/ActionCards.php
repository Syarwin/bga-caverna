<?php
namespace CAV\Managers;

use CAV\Core\Globals;
use CAV\Managers\Meeples;

/* Class to manage all the cards for Agricola */

class ActionCards extends \CAV\Helpers\Pieces
{
  protected static $table = 'cards';
  protected static $prefix = 'card_';
  protected static $customFields = ['extra_datas'];
  protected static $autoremovePrefix = false;
  protected static $autoIncrement = false;

  protected static function cast($card)
  {
    $className = '\CAV\ActionCards\\' . $card['card_id'];
    return new $className($card);
  }

  protected static $actionCards = [
    'Clearing',
    'Clearing4',
    'Depot',
    'DriftMining',
    'DriftMining4',
    'Excavation',
    'Excavation4',
    'Extension',
    'FenceBuilding',
    'FenceBuilding6',
    'FirstPlayer',
    'FirstPlayer4',
    'ForestExploration',
    'ForestExploration4',
    'HardwareRental',
    'HardwareRental6',
    'Housework',
    'Imitation',
    'Imitation5',
    'Imitation7',
    'LargeDepot',
    'Logging',
    'Logging4',
    'OreMining',
    'OreMining4',
    'RubyMining',
    'Slash',
    'SmallDriftMining',
    'StripMining',
    'Supplies',
    'Growth',
    'Sustenance',
    'Sustenance4',
    'WeeklyMarket',
    'WoodGathering',

    // Stage 1
    'Blacksmithing',
    'OreMineConstruction',
    'SheepFarming',
    // Stage 2
    'DonkeyFarming',
    'RubyMineConstruction',
    'WishChildren',
    // Stage 3
    'FamilyLife',
    'Exploration',
    'OreDelivery',
    // Stage 4
    'Adventure',
    'OreTrading',
    'RubyDelivery',
    'UrgentWishChildren',
  ];

  /* Creation of the cards */
  public static function setupNewGame($players, $options)
  {
    $cards = [];
    foreach (self::$actionCards as $class) {
      $className = '\CAV\ActionCards\Action' . $class;
      $card = new $className(null);

      // Check number of players and options constraints
      if (!$card->isSupported($players, $options)) {
        continue;
      }

      // Compute location depending on number of players
      $location = self::getInitialLocation($card, $players);

      $cards[] = [
        'id' => $card->getId(),
        'location' => $location,
        'state' => $location == 'board' ? 1 : 0,
      ];
    }

    self::create($cards, null);

    if (count($players) > 1) {
      $turn = 1;

      for ($i = 1; $i <= 4; $i++) {
        self::shuffle('deck_' . $i);

        foreach (self::getInLocationOrdered('deck_' . $i) as $id => $card) {
          // WishChildren is already placed
          if ($turn != 4) {
            self::move($card->getId(), 'turn_' . $turn);
          }
          $turn++;
          if ($turn == 4) {
            $turn++;
          }
        }
      }
    }
  }

  public static function getInitialLocation($card, $players)
  {
    $stage = $card->getStage();
    if ($stage == 0) {
      return 'board';
    }

    // Handle solo
    if (count($players) == 1) {
      $mapping = [
        'ActionBlacksmithing' => 1,
        'ActionSheepFarming' => 2,
        'ActionOreMineConstruction' => 3,
        'ActionWishChildren' => 4,
        'ActionDonkeyFarming' => 5,
        'ActionRubyMineConstruction' => 6,
        'ActionOreDelivery' => 7,
        'ActionFamilyLife' => 8,
        'ActionOreTrading' => 9,
        'ActionAdventure' => 10,
        'ActionRubyDelivery' => 11,
      ];
      return ['turn', $mapping[$card->getId()]];
    }
    // WishChildren is always at round 4
    elseif ($card->getId() == 'ActionWishChildren') {
      return ['turn', 4];
    }
    // 2 players has no round 9
    elseif (count($players) <= 2 && $stage > 9) {
      return ['deck', $stage - 1];
    } else {
      return ['deck', $stage];
    }
  }

  public static function getVisible($player = null)
  {
    return self::getInLocation('board')->merge(self::getInLocation(['turn', '%'], VISIBLE));
  }

  public static function getAccumulationSpaces($type = null, $player = null)
  {
    $cards = self::getInLocation('board')->merge(self::getInLocation(['turn', '%'], VISIBLE));
    if (isset($player)) {
      $cards = $cards->merge($player->getActionCards());
    }

    $cards = $cards->filter(function ($space) {
      return $space->getAccumulation() != null;
    });

    if (isset($type)) {
      $cards = $cards->filter(function ($space) use ($type) {
        return isset($space->getAccumulation()[$type]);
      });
    }

    return $cards;
  }

  public static function getAccumulationSpacesWith6()
  {
    $cards = [];
    foreach (self::getAccumulationSpaces() as $cId => $card) {
      if (Meeples::getResourcesOnCard($cId)->count() >= 6) {
        $cards[] = $cId;
      }
    }
    return $cards;
  }

  public static function getUiData()
  {
    return [
      'visible' => self::getVisible()->ui(),
      'help' => self::getHelp(),
    ];
  }

  public static function getHelp()
  {
    $cards = self::getInLocation(['turn', '%'])->ui();
    $nPlayers = Players::count();
    if ($nPlayers == 1) {
      return $cards; // No need to hide cards in solo mode since order is enforced
    }

    $map = $nPlayers <= 2 ? [0, 1, 1, 1, 2, 3, 3, 4, 4, 5, 5, 5] : [0, 1, 1, 1, 2, 3, 3, 4, 4, 4, 5, 5, 5];
    foreach ($cards as &$card) {
      $turn = \explode('_', $card['location'])[1];
      $card['location'] = 'turn_' . $map[$turn];
    }
    return $cards;
  }

  public static function draw()
  {
    $turn = Globals::getTurn();
    $location = ['turn', $turn];
    if (self::countInLocation($location, HIDDEN) == 0) {
      return self::getInLocation($location);
    }

    self::moveAllInLocation($location, $location, HIDDEN, VISIBLE);
    return self::getInLocation($location);
  }

  public static function accumulate()
  {
    $ids = [];
    foreach (self::getVisible() as $id => $card) {
      $ids = array_merge($ids, $card->accumulate());
    }
    return $ids;
  }

  /**
   * Generate/load seed
   */
  public static function getSeed()
  {
    $res = '';
    foreach (self::$actionCards as $class) {
      $card = self::getSingle('Action' . $class, false);
      if ($card != null && $card->getTurn() != 0) {
        $res .= dechex($card->getTurn());
      }
    }
    return $res;
  }

  public static function setSeed($seed)
  {
    $i = 0;
    foreach (self::$actionCards as $class) {
      $card = self::getSingle('Action' . $class, false);
      if ($card != null && $card->getTurn() != 0) {
        $turn = hexdec($seed[$i++]);
        self::move($card->getId(), 'turn_' . $turn);
      }
    }
  }
}
