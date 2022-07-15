<?php
namespace CAV\States;
use CAV\Managers\ActionCards;
use CAV\Managers\Buildings;
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
    Meeples::setupNewGame($players, $options);
    Globals::setupNewGame($players, $options);
    Preferences::setupNewGame($players, $this->player_preferences);
    ActionCards::setupNewGame($players, $options);
    //    Buildings::setupNewGame($players, $options);
    Stats::checkExistence();

    $this->setGameStateInitialValue('logging', false);
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

  /**
   * Save/load seed
   */
  public function saveSeed()
  {
    $raw = Players::count() . '|' . ActionCards::getSeed() . '|' . Buildings::getSeed();
    $encoded = rtrim(strtr(base64_encode(addslashes(gzcompress($raw, 9))), '+/', '-_'), '=');
    Globals::setGameSeed($encoded);
  }

  public function actLoadSeed($seed)
  {
    $raw = gzuncompress(
      stripslashes(base64_decode(str_pad(strtr($seed, '-_', '+/'), strlen($seed) % 4, '=', STR_PAD_RIGHT)))
    );
    $data = explode('|', $raw);
    if ($data[0] != Players::count()) {
      throw new \BgaUserException(
        'Trying to load a ' . $data[0] . ' players seed in your ' . Players::count() . ' players game'
      );
    }

    // Load action cards
    ActionCards::setSeed($data[1]);

    // Load player cards
    $i = 2;
    Buildings::preSeedClear();
    foreach (Players::getAll() as $player) {
      Buildings::setSeed($player, $data[$i++]);
    }

    // Refresh UI
    $datas = $this->getAllDatas();
    Notifications::refreshUI($datas);
    foreach (Players::getAll() as $player) {
      Notifications::refreshHand($player, $player->getHand()->ui());
    }

    // Start game
    $this->gamestate->nextState('start');
  }
}
