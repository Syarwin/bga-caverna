<?php
namespace CAV\Managers;
use CAV\Core\Globals;
use CAV\Core\Stats;
use CAV\Core\Notifications;
use CAV\Managers\Buildings;

/*
 * Scores manager : allows to easily update/notify scores
 *   -> could have been inside Players.php but better structure this way
 */
class Scores extends \CAV\Helpers\DB_Manager
{
  protected static $table = 'player';
  protected static $primary = 'player_id';
  protected static function cast($row)
  {
    return new \CAV\Models\Player($row);
  }

  /*
   * Update scores UI
   */
  protected static $scores = [];
  protected static function init()
  {
    self::$scores = [];
    foreach (Players::getAll() as $pId => $player) {
      self::$scores[$pId] = [
        'total' => 0,
      ];
      foreach (SCORING_CATEGORIES as $category) {
        self::$scores[$pId][$category] = [
          'total' => 0,
          'entries' => [],
        ];
      }
    }
  }

  /**
   * Add a scoring entry in the corresponding category
   */
  public static function addEntry($player, $category, $score, $qty = null, $source = null)
  {
    $pId = is_int($player) ? $player : $player->getId();
    // Add entry
    $data = [
      'score' => $score,
    ];
    if (!is_null($qty)) {
      $data['quantity'] = $qty;
    }
    if (!is_null($source)) {
      $data['source'] = $source;
    }
    self::$scores[$pId][$category]['entries'][] = $data;

    // Update scores
    self::$scores[$pId][$category]['total'] += $score;
    self::$scores[$pId]['total'] += $score;
  }

  /**
   * Update every player score in DB and on UI
   */
  public function update($bypassCheck = false)
  {
    if (!$bypassCheck && !Globals::isLiveScoring()) {
      return;
    }

    $scores = self::compute();
    foreach (Players::getAll() as $pId => $player) {
      self::DB()->update(['player_score' => self::$scores[$pId]['total']], $pId);
    }

    Notifications::updateScores(self::$scores);
  }

  /**
   * Compute the scores and return them
   */
  public function compute()
  {
    self::init();
    foreach (Players::getAll() as $pId => $player) {
      self::computePlayer($player);
      if ($player->hasPlayedBuilding('Y_WritingChamber')) {
        Buildings::getFilteredQuery(null, null, 'Y_WritingChamber')
          ->get(true)
          ->computeSpecialScore(self::$scores);
      }
    }

    // update of Stats
    foreach (Players::getAll() as $pId => $player) {
      Stats::setScoreBuildings($player, self::$scores[$player->getId()][SCORING_BUILDINGS]['total']);
      Stats::setScoreBuildingsBonus($player, self::$scores[$player->getId()][SCORING_BUILDINGS_BONUS]['total']);
    }

    return self::$scores;
  }

  /**
   * Compute the score of an individual player
   */
  public function computePlayer($player)
  {
    self::computeAnimals($player);

    self::computeGrains($player);
    self::computeVegetables($player);

    self::computeRuby($player);
    self::computeDwarfs($player);

    self::computeEmptyCells($player);
    self::computePastures($player);
    self::computeMines($player);

    self::computeBuildings($player);

    self::computeGold($player);
    self::computeBeggings($player);

    self::computeAuxScore($player);
  }

  protected function computeAuxScore($player)
  {
    $aux = 0;
//    self::DB()->update(['player_score_aux' => $aux], $player->getId());
  }

  protected function computeAnimals($player)
  {
    $reserve = $player->getExchangeResources();
    foreach (ANIMALS as $type) {
      $n = $reserve[$type];
      $score = ($n == 0 && $type != DOG) ? -2 : $n;
      self::addEntry($player, $type, $score, $n);
      $statName = 'setScore' . \ucfirst($type);
      Stats::$statName($player, $score);
    }
  }

  protected function computeGrains($player)
  {
    $n = $player->countReserveAndGrowingResource(GRAIN);
    $score = ceil($n / 2);

    self::addEntry($player, SCORING_GRAINS, $score, $n);
    Stats::setScoreGrains($player, $score);
  }
  protected function computeVegetables($player)
  {
    $n = $player->countReserveAndGrowingResource(VEGETABLE);
    $score = $n;
    self::addEntry($player, SCORING_VEGETABLES, $score, $n);
    Stats::setScoreVegetables($player, $score);
  }

  protected function computeRuby($player)
  {
    $n = $player->countReserveResource(RUBY);
    $score = $n;
    self::addEntry($player, SCORING_RUBIES, $score, $n);
    Stats::setScoreRubies($player, $score);
  }
  protected function computeDwarfs($player)
  {
    $n = $player->countDwarfs();
    $score = $n;
    self::addEntry($player, SCORING_DWARFS, $score, $n);
    Stats::setScoreDwarfs($player, $score);
  }

  protected function computeEmptyCells($player)
  {
    $n = $player->board()->countEmptyCell();
    $score = $n * -1;
    self::addEntry($player, SCORING_EMPTY, $score, $n);
    Stats::setScoreUnused($player, -$score);
  }
  protected function computePastures($player)
  {
    $score = 0;
    foreach ($player->board()->getPastures() as $pasture) {
      $score += count($pasture['nodes']) == 1 ? 2 : 4;
    }
    self::addEntry($player, SCORING_PASTURES, $score);
    Stats::setScorePastures($player, $score);
  }
  protected function computeMines($player)
  {
    $score = 3 * $player->countOreMines() + 4 * $player->countRubyMines();
    self::addEntry($player, SCORING_MINES, $score);
    Stats::setScoreMines($player, $score);
  }

  protected function computeBuildings($player)
  {
    foreach ($player->getBuildings() as $building) {
      $building->computeScore();
    }
    Stats::setScoreBuildings($player, self::$scores[$player->getId()][SCORING_BUILDINGS]['total']);
    Stats::setScoreBuildingsBonus($player, self::$scores[$player->getId()][SCORING_BUILDINGS_BONUS]['total']);
  }

  protected function computeGold($player)
  {
    $n = $player->countReserveResource(GOLD);
    $score = $n;
    self::addEntry($player, SCORING_GOLD, $score);
    Stats::setScoreGold($player, $score);
  }
  protected function computeBeggings($player)
  {
    $n = $player->countReserveResource(BEGGING);
    $score = -3 * $n;
    self::addEntry($player, SCORING_BEGGINGS, $score, $n);
    Stats::setScoreBeggings($player, -$score);
  }
}
