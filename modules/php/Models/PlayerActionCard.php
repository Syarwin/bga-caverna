<?php
namespace AGR\Models;
use AGR\Helpers\Utils;
use AGR\Core\Notifications;
use AGR\Core\Engine;
use AGR\Core\Globals;
use AGR\Managers\Farmers;
use AGR\Managers\Meeples;
use AGR\Managers\Scores;
use AGR\Managers\ActionCards;
use AGR\Managers\Players;
use AGR\Managers\PlayerCards;

/*
 * PlayerCard: parent of Major/minor improvements and Occupation
 */

class PlayerActionCard extends PlayerCard
{
  protected $actionCard = true; // for C104_Collector
  protected $holder = true;

  public function isBuyable($player, $ignoreResources = false, $args = [])
  {
    // cannot be bought by another player
    if ($this->pId != $player->getId()) {
      return false;
    }

    return parent::isBuyable($player, $ignoreResources, $args);
  }

  public function getActionCardType()
  {
    return 'special';
  }

  public function hasAccumulation()
  {
    return false;
  }

  public function getAccumulation()
  {
    return [];
  }

  public function canBePlayed($player, $onlyCheckSpecificPlayer = null)
  {
    if ($player->getId() != $this->pId) {
      return false;
    }

    $farmers = Farmers::getOnCard($this->id);
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

    // Check that the action is doable
    $flow = $this->getFlow($player);
    $flowTree = Engine::buildTree($flow);
    return $flowTree->isDoable($player);
  }

  /**
   * Tag all the subtree flow with the information about this card so we can access it in the ctx later
   */
  protected function tagTree($t, $player)
  {
    $t['cardId'] = $this->id;
    $t['pId'] = $player->getId();
    if (isset($t['childs'])) {
      $t['childs'] = array_map(function ($child) use ($player) {
        return $this->tagTree($child, $player);
      }, $t['childs']);
    }
    return $t;
  }

  public function getFlow($player)
  {
    return $this->tagTree($this->flow, $player); // Add card context for listeners
  }
}
