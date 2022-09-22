<?php
namespace CAV\Actions;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Core\Game;
use CAV\Core\Globals;
use CAV\Managers\Players;
use CAV\Managers\Meeples;
use CAV\Helpers\Utils;
use CAV\Core\Stats;
use CAV\Helpers\UserException;

class Exchange extends \CAV\Models\Action
{
  public function getState()
  {
    return ST_EXCHANGE;
  }

  public function getDescription($ignoreResources = false)
  {
    $trigger = $this->getCtxArgs()['trigger'] ?? ANYTIME;
    return clienttranslate('Exchange resources');
  }

  /**
   * Allow for args as $ctx instead of a tree node => useful for harvest
   */
  public function getCtxArgs()
  {
    return $this->ctx == null ? null : (is_array($this->ctx) ? $this->ctx : $this->ctx->getArgs());
  }

  public function getTrigger()
  {
    return $this->getCtxArgs()['trigger'] ?? ($this->isHarvest() ? HARVEST : ANYTIME);
  }

  public function getExchanges($player)
  {
    $args = $this->getCtxArgs();
    $trigger = $this->getTrigger();
    $exclusive = $args['exclusive'] ?? $trigger == BREAD;
    return $args['exchanges'] ?? $player->getPossibleExchanges($trigger, $exclusive);
  }

  public function isDoable($player, $ignoreResources = false)
  {
    if ($ignoreResources) {
      return true;
    }
    $exchanges = $this->getExchanges($player);
    $resources = $player->getExchangeResources();

    // Try to find one doable exchange
    foreach ($exchanges as $exchange) {
      $ok = true;
      foreach ($exchange['from'] as $type => $amount) {
        $ok = $ok && $resources[$type] >= $amount;
      }

      if ($ok) {
        return true;
      }
    }
    return false;
  }

  public function isOptional()
  {
    return !$this->isMandatory();
  }

  public function isMandatory()
  {
    $args = $this->getCtxArgs();
    return $args['mustCook'] ?? false;
  }

  public function isAutomatic($player = null)
  {
    $args = $this->argsExchange();
    if (!$this->isMandatory()) {
      return false;
    }

    $exchanges = $this->getAnimalExchanges();
    foreach ($args['extraAnimals'] as $type => $amount) {
      if ($amount > 0 && count($exchanges[$type]) != 1) {
        return false;
      }
    }

    return true;
  }

  /*
   * FORMAT DOCUMENTATION
   * $exchanges is an array of $exchange
   *
   * $exchange : [
   *    'source' => (optional) translate string for UI
   *    'triggers' => null if at any time, of array of triggers such as BREAD, HARVEST
   *    'max' => maxAmount of time you can use this exchange
   *    'from' => [
   *        resourceType => amount,
   *          ....
   *    ],
   *    'to' => [
   *        resourceType => amount,
   *        ...
   *    ]
   * ]
   */

  public function argsExchange()
  {
    $player = Players::getActive();
    $trigger = $this->getTrigger();
    $exchanges = $this->getExchanges($player);
    $mustCook = $this->getCtxArgs()['mustCook'] ?? false;

    return [
      'exchanges' => $exchanges,
      'resources' => $player->getExchangeResources(),
      'trigger' => $trigger,
      'canGoToExchange' => false,
      'extraAnimals' => $player->countFarmAnimalsInReserve(),
      'mandatory' => $this->isMandatory(),
    ];
  }

  public function getAnimalExchanges()
  {
    $args = $this->argsExchange();
    $res = [];
    foreach ($args['extraAnimals'] as $type => $amount) {
      if ($amount == 0) {
        continue;
      }

      $exchanges = array_filter($args['exchanges'], function ($exchange) use ($type, $amount) {
        return array_keys($exchange['from']) == [$type] && ($exchange['max'] ?? 99) >= $amount;
      });
      $res[$type] = $exchanges;
    }

    return $res;
  }

  public function stExchange()
  {
    if (!$this->isAutomatic()) {
      return;
    }

    $args = $this->argsExchange();
    $exchanges = $this->getAnimalExchanges();
    $trades = [];
    foreach ($args['extraAnimals'] as $type => $amount) {
      for ($i = 0; $i < $amount; $i++) {
        $trades[] = array_keys($exchanges[$type])[0];
      }
    }

    $this->actExchange($trades);
  }

  public function actExchange($trades)
  {
    $this->checkAction('actExchange');
    $args = $this->argsExchange();
    if ($args['trigger'] == BREAD && empty($trades) && $this->isMandatory()) {
      throw new UserException(totranslate('You must exchange at least 1 grain in the bake bread action'));
    }

    $player = Players::getActive();
    $exchanges = $args['exchanges'];
    $reserve = $args['resources'];
    $nbExchanges = array_map(function ($a) {
      return 0;
    }, $exchanges);
    $deleted = [];
    $created = [];
    $eventData = [
      'trigger' => $this->getTrigger(),
      'exchanges' => $exchanges,
      'trades' => $trades,
      'created' => $created,
    ];

    for ($i = 0; $i < count($trades); $i++) {
      $tradeIndex = $trades[$i];
      $stat = [];
      // check exchange is authorized and exist
      $exchange = $exchanges[$tradeIndex] ?? null;
      if ($exchange == null) {
        throw new \BgaUserException('Exchange not possible. Should not happen');
      }

      // Check that trade is not executed too much
      $nbExchanges[$tradeIndex]++;
      if ($nbExchanges[$tradeIndex] > ($exchange['max'] ?? INFTY)) {
        throw new \BgaUserException('Too many exchanges of type. Should not happen');
      }

      // Check the player can afford exchange
      foreach ($exchange['from'] as $res => $amount) {
        $reserve[$res] -= $amount;
        if ($reserve[$res] < 0) {
          throw new \BgaUserException('Using too much resource. Should not happen');
        }
        $stat[$res] = $amount;
        array_push($deleted, ...$player->useResource($res, $amount));
      }

      // Create new resources
      foreach ($exchange['to'] as $res => $amount) {
        $reserve[$res] += $amount;
        array_push($created, ...$player->createResourceInReserve($res, $amount));

        if ($res == FOOD) {
          foreach ($stat as $key => $value) {
            $f = 'incConverted' . \ucfirst($key);
            Stats::$f($player, $value);

            $conv = 'inc' . \ucfirst($key) . 'ToFood';
            Stats::$conv($player, $amount);
          }
        }
      }

      // Flag if needed
      if (\array_key_exists('flag', $exchange) && $exchange['flag'] != null) {
        $flags = Globals::getExchangeFlags();
        $flags[] = $exchange['flag'];
        Globals::setExchangeFlags($flags);
      }

      // Notify (group consecutive identical exchange)
      if ($i == count($trades) - 1 || $trades[$i + 1] != $tradeIndex) {
        if (count($deleted) != 0 || count($created) != 0) {
          Notifications::exchange($player, $deleted, $created, $exchange['source'] ?? null);
        }
        array_push($eventData['created'], ...$created);
        $deleted = [];
        $created = [];
      }
    }

    if ($this->isMandatory()) {
      // If it was mandatory, discard excess animals
      $animals = $player->getAllReserveResources();
      foreach (FARM_ANIMALS as $type) {
        if ($animals[$type] != 0) {
          Notifications::discardAnimals($player, $player->useResource($type, $animals[$type]));
        }
      }
    } else {
      // If we have traded an animal we need to reorganizes
      $player->checkAnimalsInReserve();
    }

    Notifications::updateDropZones($player);
    $player->forceReorganizeIfNeeded();

    $this->checkAfterListeners($player, $eventData);
    $this->resolveAction(['trades' => $trades]);
  }
}
