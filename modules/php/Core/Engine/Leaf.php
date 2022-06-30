<?php
namespace AGR\Core\Engine;
use AGR\Managers\Actions;

/*
 * Leaf: a class that represent a Leaf
 */
class Leaf extends AbstractNode
{
  public function __construct($infos = [])
  {
    parent::__construct($infos, []);
    $this->infos['type'] = NODE_LEAF;
  }

  /**
   * An action leaf is resolved as soon as the action is resolved
   */
  public function isResolved()
  {
    return parent::isResolved() || ($this->getAction() != null && $this->isActionResolved());
  }


  /**
   * A Leaf is doable if the corresponding action is doable by the player
   */
  public function isDoable($player)
  {
    if (isset($this->infos['action'])) {
      return $player->canTakeAction($this->infos['action'], $this);
    }
    throw new \BgaVisibleSystemException('Unimplemented isDoable function for non-action Leaf');
  }

  /**
   * The state is either hardcoded into the leaf, or correspond to the attached action
   */
  public function getState()
  {
    if (isset($this->infos['state'])) {
      return $this->infos['state'];
    }

    if (isset($this->infos['action'])) {
      return Actions::getState($this->infos['action'], $this);
    }

    throw new \BgaVisibleSystemException('Trying to get state on a leaf without state nor action');
  }

  /**
   * The description is given by the corresponding action
   */
  public function getDescription()
  {
    if (isset($this->infos['action'])) {
      return Actions::get($this->infos['action'], $this)->getDescription();
    }
    return parent::getDescription();
  }
}
