<?php
namespace CAV\Models;
use CAV\Helpers\Utils;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Core\Globals;
use CAV\Core\Stats;
use CAV\Managers\Meeples;
use CAV\Managers\Scores;
use CAV\Managers\ActionCards;
use CAV\Managers\Players;
use CAV\Managers\Buildings;

/*
 * PlayerCard: parent of Major/minor improvements and Occupation
 */

class Building extends AbstractBuilding
{
  protected $implemented = true; // For DEV only

  protected $number = null;
  protected $players = null;
  protected $banned = false;

  protected $desc = []; // UI
  protected $costText = ''; // UI
  protected $prerequisite = ''; // UI
  protected $holder = false; // Is holding resources ?

  // protected $vp = 0;
  protected $extraVp = false;
  // protected $cost = [];
  protected $fee = null;
  protected $exchanges = [];
  protected $category = null;
  protected $type = null;

  // protected $actionCard = false; // for C104_Collector
  protected $flow = null;

  public function jsonSerialize()
  {
    $data = parent::jsonSerialize();
    $data['players'] = $this->players;
    $data['desc'] = $this->desc;
    $data['extraVp'] = $this->extraVp;
    $data['bonusVp'] = $this->getBonusScore() > 0 ? $this->getBonusScore() : '';
    $data['costs'] = $this->costs ?? [$this->cost];
    $data['costText'] = $this->costText;
    $data['fee'] = $this->fee;
    $data['type'] = $this->type;
    $data['category'] = $this->category;
    $data['numbering'] = str_pad($this->number, 3, '0', STR_PAD_LEFT);
    $data['holder'] = $this->holder;
    $data['animalHolder'] = $this->animalHolder;
    $data['field'] = $this->field;
    return $data;
  }

  public function isSupported($players, $options)
  {
    // Check number of players
    $nPlayers = count($players);
    $checkPlayer =
      $this->players === null ||
      (is_array($this->players) && in_array($nPlayers, $this->players)) || // TODO : we can probably remove that since players criterion are always X+ on cards
      (is_string($this->players) && $nPlayers >= (int) explode('+', $this->players)[0]);

    // Check banlist
    $checkBanlist = !$this->banned; // TODO reenable? || $options[OPTION_COMPETITIVE_LEVEL] != OPTION_COMPETITIVE_BANLIST;

    return $this->implemented && $checkPlayer && $checkBanlist;
  }

  /**
   * Useful for notifications
   */
  public function getTypeStr()
  {
    return '';
  }
}
