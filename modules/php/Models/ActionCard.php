<?php
namespace CAV\Models;

use CAV\Managers\Meeples;
use CAV\Managers\Dwarfs;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Helpers\Utils;

/*
 * Action cards for all
 */

class ActionCard extends \CAV\Helpers\DB_Model
{
  protected $table = 'cards';
  protected $primary = 'card_id';
  protected $attributes = [
    'id' => ['card_id', 'int'],
    'location' => 'card_location',
    'state' => ['card_state', 'int'],
    'extraDatas' => ['extra_datas', 'obj'],
  ];

  /*
   * STATIC INFORMATIONS
   *  they are overwritten by children
   */
  protected $staticAttributes = ['name', 'tooltip', 'desc'];
  protected $name = '';
  protected $tooltip = [];
  protected $desc = []; // UI

  protected $actionCardType = null; // Useful to declare Hollow4 as an Hollow action
  protected $stage = 0;
  protected $accumulation = []; // Array of resource => amount
  // Constraints
  protected $players = null; // Players requirements => null if none, integer if only one, array otherwise

  public function getUiData()
  {
    return array_merge(parent::getUiData(), [
      'accumulate' => count($this->accumulation) > 0,
      'component' => $this->isBoardComponent(),
      'desc' => $this->getDesc(),
    ]);
  }

  public function isSupported($players, $options)
  {
    return $this->players == null || in_array(count($players), $this->players);
  }

  public function getActionCardType()
  {
    return $this->actionCardType ?? substr($this->id, 6);
  }

  public function getStage()
  {
    return $this->stage;
  }

  public function getDesc()
  {
    return $this->desc;
  }

  public function getTurn()
  {
    $loc = $this->getLocation();
    $t = explode('_', $loc);
    if ($t[0] != 'turn') {
      return 0;
    }

    return (int) $t[1];
  }

  public function isBoardComponent()
  {
    return $this->stage == 0;
  }

  public function hasAccumulation()
  {
    if (count($this->accumulation) == 0) {
      return false;
    }
    return true;
  }

  public function getAccumulation()
  {
    return $this->accumulation;
  }

  public function accumulate()
  {
    $ids = [];
    if ($this->hasAccumulation()) {
      $n = Meeples::getResourcesOnCard(self::getId())->count();
      foreach ($this->accumulation as $resource => $amount) {
        if (is_array($amount)) {
          $amount = $n == 0 ? $amount[0] : $amount[1];
        }
        if ($amount > 0) {
          $ids = array_merge($ids, Meeples::createResourceOnCard($resource, self::getId(), $amount));
        }
      }
    }
    return $ids;
  }

  public function payGainNode($cost, $gain, $sourceName = null, $optional = true, $pId = null)
  {
    return [
      'type' => NODE_SEQ,
      'optional' => $optional,
      'pId' => $pId,
      'childs' => [$this->payNode($cost, $sourceName), $this->gainNode($gain, $pId)],
    ];
  }

  public function gainNode($gain, $pId = null)
  {
    $gain['pId'] = $pId;
    return [
      'action' => GAIN,
      'args' => $gain,
      'source' => $this->name,
      'cardId' => $this->getId(),
    ];
  }

  public function payNode($cost, $sourceName = null, $nb = 1, $to = null, $pId = null)
  {
    return [
      'action' => PAY,
      'args' => [
        'pId' => $pId,
        'nb' => $nb,
        'costs' => Utils::formatCost($cost),
        'source' => $sourceName ?? $this->name,
        'to' => $to,
      ],
    ];
  }

  public function canBePlayed($player, $dwarf, $onlyCheckSpecificPlayer = null, $ignoreResources = false)
  {
    // Is there a dwarf here ?
    $dwarfs = Dwarfs::getOnCard($this->id);
    if ($dwarfs->count() > 0 && $onlyCheckSpecificPlayer == null) {
      return false;
    }

    $pIds = $dwarfs
      ->map(function ($dwarf) {
        return $dwarf['pId'];
      })
      ->toArray();
    if (in_array($onlyCheckSpecificPlayer, $pIds)) {
      return false;
    }

    // Check that the action is doable
    $flow = $this->getTaggedFlow($player, $dwarf);
    $flowTree = Engine::buildTree($flow);
    return $flowTree->isDoable($player, $ignoreResources);
  }

  public function canBeCopied($player, $dwarf, $ignoreResources = false)
  {
    // Is there a dwarf here ?
    $dwarfs = Dwarfs::getOnCard($this->id)->filter(function ($d) use ($player) {
      return $d['pId'] != $player->getId();
    });
    if ($dwarfs->count() == 0) {
      return false;
    }

    // Check that the action is doable
    $flow = $this->getTaggedFlow($player, $dwarf);
    $flowTree = Engine::buildTree($flow);
    return $flowTree->isDoable($player, $ignoreResources);
  }

  protected function getFlow($player, $dwarf)
  {
    return $this->flow;
  }

  public function getTaggedFlow($player, $dwarf)
  {
    // Add card context for listeners
    return Utils::tagTree($this->getFlow($player, $dwarf), [
      'pId' => $player->getId(),
      'cardId' => $this->id,
      'dwarfId' => $dwarf['id'] ?? null,
    ]);
  }
}
