<?php

namespace CAV\States;

use CAV\Managers\ActionCards;
use CAV\Managers\Buildings;
use CAV\Managers\Tiles;
use CAV\Managers\Players;
use CAV\Managers\Meeples;
use CAV\Core\Notifications;
use CAV\Core\Preferences;
use CAV\Core\Globals;
use CAV\Core\Stats;

trait SetupTrait
{
  /*
   * setupNewGame:
   */
  protected function setupNewGame($players, $options = [])
  {
    Players::setupNewGame($players, $options);
    Globals::setupNewGame($players, $options);
    Meeples::setupNewGame($players, $options);
    Preferences::setupNewGame($players, $this->player_preferences);
    ActionCards::setupNewGame($players, $options);
    Buildings::setupNewGame($players, $options);
    Tiles::setupNewGame($players, $options);
    Stats::checkExistence();

    $this->setGameStateInitialValue('logging', false);
    $this->activeNextPlayer();
  }

  /**
   * Start the game
   */
  function stStartGame()
  {
    // If "load seed" mode is selected, skip to this phase
    if (false) {
      // TODO : Globals::getDraftMode() == OPTION_SEED_MODE) {
      $this->gamestate->setAllPlayersMultiactive();
      $this->gamestate->nextState('seed');
    } else {
      // TODO
      //      $this->saveSeed();
      $this->gamestate->nextState('noDraft');
    }
  }
}
