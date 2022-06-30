<?php
namespace AGR\Managers;
use AGR\Core\Globals;
use AGR\Core\Game;
use AGR\Helpers\Utils;

/* Class to manage all the cards for Agricola */
// PlayerCards in contrast to ActionCards that are the cards on the board

class PlayerCards extends \AGR\Helpers\Pieces
{
  protected static $table = 'cards';
  protected static $prefix = 'card_';
  protected static $customFields = ['player_id', 'extra_datas'];
  protected static $autoIncrement = false;

  protected static function cast($card)
  {
    return self::getCardInstance($card['id'], $card);
  }

  public static function getCardInstance($id, $data = null)
  {
    $t = explode('_', $id);
    // First part before _ specify the deck and the numbering
    // Eg: Major_Fireplace1,  A24_ThreshingBoard, ...
    $prefix = $t[0] == 'Major' ? 'Major' : $t[0][0];
    $className = "\AGR\Cards\\$prefix\\$id";
    return new $className($data);
  }

  /* Creation of the cards */
  public static function setupNewGame($players, $options)
  {
    // Load list of cards
    include dirname(__FILE__) . '/../Cards/list.inc.php';

    // Keep only the supported cards and group them by type
    $cards = [
      MAJOR => [],
      MINOR => [],
      OCCUPATION => [],
    ];
    foreach ($cardIds as $cId) {
      $card = self::getCardInstance($cId);
      if ($card->isSupported($players, $options)) {
        $cards[$card->getType()][$card->getId()] = [
          'id' => $card->getId(),
          'location' => 'box',
        ];
      }
    }

    // Put the Major Improvements on the board
    foreach ($cards[MAJOR] as &$card) {
      $card['location'] = 'board';
    }

    // If Draft mode is disabled
    if (!Globals::isBeginner() && Globals::getDraftMode() != OPTION_SEED_MODE) {
      if (Globals::getDraftMode() == OPTION_DRAFT_DISABLED) {
        foreach ($players as $pId => $player) {
          self::drawCards($cards[MINOR], $pId, 7);
          self::drawCards($cards[OCCUPATION], $pId, 7);
        }
      } else {
        $n = Game::get()->getDraftStartingNumberOfCards();
        $nMinor = $n == -1 ? count($cards[MINOR]) : $n;
        $nOccupation = $n == -1 ? count($cards[OCCUPATION]) : $n;
        foreach ($players as $pId => $player) {
          self::drawCards($cards[MINOR], $pId, $nMinor, 'draft');
          self::drawCards($cards[OCCUPATION], $pId, $nOccupation, 'draft');
        }
      }
    }

    // Merge cards to be created
    $oCards = array_merge(array_values($cards[MAJOR]), array_values($cards[MINOR]), array_values($cards[OCCUPATION]));

    // Remove cards still in the box
    $oCards = array_filter($oCards, function ($card) {
      return $card['location'] != 'box';
    });

    // Create the cards
    self::create($oCards, null);
  }

  public static function drawCards(&$cards, $pId, $n, $location = 'hand')
  {
    $pool = array_filter($cards, function ($card) {
      return $card['location'] == 'box';
    });
    $hand = array_rand($pool, $n);
    foreach ($hand as $cId) {
      $cards[$cId]['location'] = $location;
      $cards[$cId]['player_id'] = $pId;
    }
  }

  /**************************
   * Draft related functions *
   ***************************/
  public function addToSelection($card)
  {
    // Compute position to ensure occupations and minors are apart
    $cards = self::getInLocationQ('hand')
      ->wherePlayer($card->getPId())
      ->get();
    $pos = $cards->reduce(
      function ($carry, $c) use ($card) {
        return $card->getType() == $c->getType() ? max($carry, $c->getState()) : $carry;
      },
      $card->getType() == OCCUPATION ? 0 : 10
    );

    //    $pos = self::getExtremePosition(true, 'hand');
    self::move($card->getId(), 'selection', $pos + 1);
    return $pos + 1;
  }

  public function removeFromSelection($card)
  {
    self::move($card->getId(), 'draft');
  }

  public function confirmDraftSelections()
  {
    $cards = self::getInLocation('selection');
    self::moveAllInLocationKeepState('selection', 'hand');
    self::moveAllInLocation('draft', 'passing');
    return $cards;
  }

  public function passCards()
  {
    // Pass non-selected cards to next player
    foreach (self::getInLocation('passing') as $card) {
      self::DB()->update(
        [
          'card_location' => 'draft',
          'player_id' => Players::getNextId($card->getPId()),
        ],
        $card->getId()
      );
    }
  }

  /*
   * Add base filter to remove all action cards
   */
  protected static function addBaseFilter(&$query)
  {
    $query = $query->where('card_id', 'NOT LIKE', 'Action%');
  }

  public static function getUiData()
  {
    return self::getInLocationOrdered('board')
      ->merge(self::getInLocationOrdered('inPlay'))
      ->ui();
  }

  public static function getOfPlayer($pId)
  {
    return self::getSelectQuery()
      ->wherePlayer($pId)
      ->where('card_location', '<>', 'passing')
      ->get();
  }

  public static function getAvailables($type = null)
  {
    $location = 'hand';
    if ($type == MAJOR) {
      $location = 'board';
    }

    return self::getInLocation($location)->filter(function ($card) use ($type) {
      return !$card->isPlayed() && ($type == null || $card->getType() == $type);
    });
  }

  /**
   * Get all the cards triggered by an event
   */
  public function getListeningCards($event)
  {
    return self::getInLocation('inPlay')
      ->merge(self::getInLocation('hand'))
      ->filter(function ($card) use ($event) {
        return $card->isListeningTo($event);
      })
      ->getIds();
  }

  /**
   * Get reaction in form of a PARALLEL node with all the activated card
   */
  public function getReaction($event, $returnNullIfEmpty = true)
  {
    $listeningCards = self::getListeningCards($event);
    if (empty($listeningCards) && $returnNullIfEmpty) {
      return null;
    }

    $childs = [];
    $passHarvest = Globals::isHarvest() ? Globals::getSkipHarvest() ?? [] : [];
    foreach ($listeningCards as $cardId) {
      if (
        in_array(
          self::get($cardId)
            ->getPlayer()
            ->getId(),
          $passHarvest
        )
      ) {
        continue;
      }

      $childs[] = [
        'action' => ACTIVATE_CARD,
        'pId' => $event['pId'],
        'args' => [
          'cardId' => $cardId,
          'event' => $event,
        ],
      ];
    }

    if (empty($childs) && $returnNullIfEmpty) {
      return null;
    }

    return [
      'type' => NODE_PARALLEL,
      'pId' => $event['pId'],
      'childs' => $childs,
    ];
  }

  /**
   * Go trough all played cards to apply effects
   */
  public function getAllCardsWithMethod($methodName)
  {
    return self::getInLocation('inPlay')->filter(function ($card) use ($methodName) {
      return \method_exists($card, 'on' . $methodName) ||
        \method_exists($card, 'onPlayer' . $methodName) ||
        \method_exists($card, 'onOpponent' . $methodName);
    });
  }

  public function applyEffects($player, $methodName, &$args)
  {
    // Compute a specific ordering if needed
    $cards = self::getAllCardsWithMethod($methodName)->toAssoc();
    $nodes = array_keys($cards);
    $edges = [];
    $orderName = 'order' . $methodName;
    foreach ($cards as $cId => $card) {
      if (\method_exists($card, $orderName)) {
        foreach ($card->$orderName() as $constraint) {
          $cId2 = $constraint[1];
          if (!in_array($cId2, $nodes)) {
            continue;
          }
          $op = $constraint[0];

          // Add the edge
          $edge = [$op == '<' ? $cId : $cId2, $op == '<' ? $cId2 : $cId];
          if (!in_array($edge, $edges)) {
            $edges[] = $edge;
          }
        }
      }
    }
    $topoOrder = Utils::topological_sort($nodes, $edges);
    $orderedCards = [];
    foreach ($topoOrder as $cId) {
      $orderedCards[] = $cards[$cId];
    }

    // Apply effects
    $result = false;
    foreach ($orderedCards as $card) {
      $res = self::applyEffect($card, $player, $methodName, $args, false);
      $result = $result || $res;
    }
    return $result;
  }

  public function applyEffect($card, $player, $methodName, &$args, $throwErrorIfNone = false)
  {
    $card = $card instanceof \AGR\Models\PlayerCard ? $card : self::get($card);
    $res = null;
    $listened = false;
    if ($player != null && $player->getId() == $card->getPId() && \method_exists($card, 'onPlayer' . $methodName)) {
      $n = 'onPlayer' . $methodName;
      $res = $card->$n($player, $args);
      $listened = true;
    } elseif (
      $player != null &&
      $player->getId() != $card->getPId() &&
      \method_exists($card, 'onOpponent' . $methodName)
    ) {
      $n = 'onOpponent' . $methodName;
      $res = $card->$n($player, $args);
      $listened = true;
    } elseif (\method_exists($card, 'on' . $methodName)) {
      $n = 'on' . $methodName;
      $res = $card->$n($player, $args);
      $listened = true;
    } elseif ($card->isAnytime($args) && \method_exists($card, 'atAnytime')) {
      $res = $card->atAnytime($player, $args);
      $listened = true;
    }

    if ($throwErrorIfNone && !$listened) {
      throw new \BgaVisibleSystemException(
        'Trying to apply effect of a card without corresponding listener : ' . $methodName . ' ' . $card->getId()
        //print_r(\debug_print_backtrace())
      );
    }

    return $res;
  }

  public static function getFieldCards($pId)
  {
    return self::getInLocationQ('inPlay')
      ->wherePlayer($pId)
      ->get()
      ->filter(function ($card) {
        return $card->isField();
      });
  }

  /**
   * Generate/load seed
   */
  public static function getSeed()
  {
    $res = '';
    foreach (Players::getAll() as $player) {
      $ids = $player
        ->getHand()
        ->map(function ($card) {
          return $card->getDeck() . dechex($card->getNumber());
        })
        ->toArray();
      $res .= ($res != '' ? '|' : '') . implode('', $ids);
    }
    return $res;
  }

  public static function preSeedClear()
  {
    self::DB()
      ->delete()
      ->whereNotNull('player_id')
      ->run();
  }

  public static function setSeed($player, $seed)
  {
    // Extract the list of (deck, number) identifiers
    preg_match_all('/([ABCD][0-9a-f]+)/', $seed, $out, PREG_PATTERN_ORDER);
    $cards = [];
    foreach ($out[1] as $card) {
      $deck = $card[0];
      $number = hexdec(\substr($card, 1));
      $cards[] = $deck . $number;
    }

    // Create the cards
    $values = [];
    include dirname(__FILE__) . '/../Cards/list.inc.php';
    foreach ($cardIds as $cId) {
      $card = self::getCardInstance($cId);
      if (in_array($card->getDeck() . $card->getNumber(), $cards)) {
        $values[] = [
          'id' => $card->getId(),
          'location' => 'hand',
          'player_id' => $player->getId(),
        ];
      }
    }
    self::create($values, null);
  }
}
