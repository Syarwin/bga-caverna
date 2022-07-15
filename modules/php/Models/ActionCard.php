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

class ActionCard extends \CAV\Models\AbstractCard
{
  /*
   * STATIC INFORMATIONS
   *  they are overwritten by children
   */
  protected $type = ACTION;
  protected $actionCardType = null; // Useful to declare Hollow4 as an Hollow action
  protected $stage = 0;
  protected $accumulation = []; // Array of resource => amount
  protected $desc = []; // UI
  protected $tooltipDesc = null; // UI
  protected $size = 'm'; // UI
  protected $container = 'central'; // UI
  protected $accumulate = ''; // UI

  // Constraints
  protected $players = null; // Players requirements => null if none, integer if only one, array otherwise
  protected $isAdditional = false;
  protected $isBeginner = false; // Will ONLY be there on the beginner variant
  protected $isNotBeginner = false; // Will NOT be there on the beginner variant

  /*
   * DYNAMIC INFORMATIONS
   */
  protected $visible = false;

  public function __construct($row)
  {
    parent::__construct($row);
    if ($row != null) {
      $this->visible = $row['location'] == 'board'; // TODO
    }
  }

  public function jsonSerialize()
  {
    $data = parent::jsonSerialize();
    $data['component'] = $this->isBoardComponent();
    $data['container'] = $this->container;
    $data['desc'] = $this->desc;
    $data['tooltipDesc'] = $this->tooltipDesc ?? $this->desc;
    $data['size'] = $this->size;
    $data['accumulate'] = $this->accumulate;

    return $data;
  }

  public function getActionCardType()
  {
    return $this->actionCardType ?? substr($this->id, 6);
  }

  public function isSupported($players, $options)
  {
    return ($this->players == null || in_array(count($players), $this->players)) &&
      (!$this->isAdditional || $options[OPTION_ADDITIONAL_SPACES] == OPTION_ADDITIONAL_SPACES_ENABLED) &&
      (!$this->isBeginner || $options[OPTION_COMPETITIVE_LEVEL] == OPTION_COMPETITIVE_BEGINNER) &&
      (!$this->isNotBeginner || $options[OPTION_COMPETITIVE_LEVEL] != OPTION_COMPETITIVE_BEGINNER);
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
        $ids = array_merge($ids, Meeples::createResourceOnCard($resource, self::getId(), $amount));
      }
    }
    return $ids;
  }
  
  public function canBePlayed($player, $onlyCheckSpecificPlayer = null, $ignoreResources = false)
  {
    // What cards should we check ?
    $actionList = [$this->id];
    if ($this->isAdditional) {
      $actionList = array_merge($actionList, [
        'ActionResourceMarketAdd',
        'ActionCopseAdd',
        'ActionAnimalMarketAdd',
        'ActionWishChildrenAdd',
      ]);
    }

    // Is there a farmer here ?
    foreach ($actionList as $action) {
      $farmers = Dwarves::getOnCard($action);
      if ($farmers->count() > 0 && $onlyCheckSpecificPlayer == null) {
        return false;
      }

      $pIds = $farmers
        ->map(function ($farmer) {
          return $farmer['pId'];
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
