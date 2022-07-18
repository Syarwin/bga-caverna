<?php
namespace CAV\Models;

use CAV\Managers\Meeples;
use CAV\Managers\Dwarves;
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
  protected $staticAttributes = ['name', 'tooltip', 'text'];
  protected $name = '';
  protected $tooltip = [];
  protected $desc = []; // UI
  protected $text = []; // Text of the card, needed for front
  protected $actionCardType = null; // Useful to declare Hollow4 as an Hollow action
  protected $stage = 0;
  protected $accumulation = []; // Array of resource => amount
  protected $container = 'central'; // UI
  // Constraints
  protected $players = null; // Players requirements => null if none, integer if only one, array otherwise


  public function jsonSerialize()
  {
    return [
      'id' => $this->id,
      'name' => $this->name,
      'location' => $this->location,
      'state' => $this->state,
      'tooltip' => $this->tooltip,

      'component' => $this->isBoardComponent(),
      // 'desc' => $this->desc,
      'container' => $this->container,
    ];
  }

  public function isSupported($players, $options)
  {
    return ($this->players == null || in_array(count($players), $this->players));
  }

  public function getActionCardType()
  {
    return $this->actionCardType ?? substr($this->id, 6);
  }

  public function getInitialLocation()
  {
    return $this->stage == 0 ? 'board' : ['deck', $this->stage];
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
      foreach ($this->accumulation as $resource => $amount) {
        if(is_array($amount)){
          $n = Meeples::getResourcesOnCard(self::getId())->count();
          $amount = $n == 0? $amount[0] : $amount[1];
        }
        $ids = array_merge($ids, Meeples::createResourceOnCard($resource, self::getId(), $amount));
      }
    }
    return $ids;
  }

  public function canBePlayed($player, $onlyCheckSpecificPlayer = null, $ignoreResources = false)
  {
    // What cards should we check ?
    $actionList = [$this->id];

    // Is there a dwarf here ?
    foreach ($actionList as $action) {
      $dwarves = Dwarves::getOnCard($action);
      if ($dwarves->count() > 0 && $onlyCheckSpecificPlayer == null) {
        return false;
      }

      $pIds = $dwarves
        ->map(function ($dwarf) {
          return $dwarf['pId'];
        })
        ->toArray();
      if (in_array($onlyCheckSpecificPlayer, $pIds)) {
        return false;
      }
    }

    // Check that the action is doable
    $flow = $this->getFlow($player);
    $flowTree = Engine::buildTree($flow);
    return $flowTree->isDoable($player, $ignoreResources);
  }


  public function getFlow($player)
  {
    // Add card context for listeners
    return Utils::tagTree($this->flow, [
      'pId' => $player->getId(),
      'cardId' => $this->id,
    ]);
  }
}
