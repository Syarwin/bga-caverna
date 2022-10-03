<?php
namespace CAV\Actions;

use CAV\Managers\Meeples;
use CAV\Managers\Players;
use CAV\Core\Notifications;
use CAV\Core\Globals;
use CAV\Core\Engine;
use CAV\Helpers\Utils;
use CAV\Core\Stats;

class HarvestChoice extends \CAV\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = clienttranslate('Choice for special harvest');
  }

  public function getState()
  {
    return ST_HARVEST_CHOICE;
  }

  public function argsHarvestChoice()
  {
    $player = Players::getActive();

    return [
      'possibilities' => [REAP, BREED],
    ];
  }

  public function actHarvestChoice($choice)
  {
    self::checkAction('actHarvestChoice');
    $player = Players::getActive();
    if (!in_array($choice, $this->argsHarvestChoice()['possibilities'])) {
      throw new \BgaVisibleSystemException('Unknown choice. Should not happen');
    }
    $choices = Globals::getHarvestChoice();
    if (is_null($choice)) {
      $choice = [];
    }

    $choices[$player->getId()] = $choice;
    Globals::setHarvestChoice($choices);

    $phases = [
      REAP => clienttranslate("the Field phase"),
      BREED => clienttranslate("the Breeding phase"),
    ];

    Notifications::message(clienttranslate('${player_name} chooses to do ${phase} for this harvest'), [
      'i18n' => ['phase'],
      'player' => $player,
      'phase' => $phases[$choice],
    ]);
    $this->resolveAction();
  }
}
