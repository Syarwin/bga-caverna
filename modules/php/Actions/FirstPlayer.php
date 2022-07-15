<?php
namespace CAV\Actions;

use CAV\Managers\Meeples;
use CAV\Core\Globals;
use CAV\Core\Engine;
use CAV\Core\Notifications;
use CAV\Managers\Players;

class FirstPlayer extends \CAV\Models\Action
{
  public function getState()
  {
    return ST_FIRSTPLAYER;
  }

  public function stFirstPlayer()
  {
    $activePlayer = Players::getActiveId();
    $tokenId = Meeples::collectFirstPlayerToken($activePlayer);
    Globals::setFirstPlayer($activePlayer);
    Notifications::firstPlayer(Players::getActive(), $tokenId);
    $this->resolveAction();
  }

  public function isAutomatic($player = null)
  {
    return true;
  }
}
