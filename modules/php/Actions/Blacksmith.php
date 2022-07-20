<?php
namespace CAV\Actions;

use CAV\Managers\Meeples;
use CAV\Managers\Players;
use CAV\Managers\Dwarves;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Core\Stats;
use CAV\Helpers\Utils;

class Blacksmith extends \CAV\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = clienttranslate('Forge a Weapon');
  }

  public function getState()
  {
    return ST_BLACKSMITH;
  }

  public function isDoable($player, $ignoreResources = false)
  {
    return ($ignoreResources || $this->maximumForgeableWeapon($player) > 0) && ($this->getDwarf()['weapon'] ?? 0) == 0;
  }

  public function maximumForgeableWeapon($player)
  {
    // TODO handle BlackSmith building
    return min(8, $player->getReserveResource(ORE)->count());
  }

  public function argsBlacksmith()
  {
    $player = Players::getActive();
    return [
      'max' => $this->maximumForgeableWeapon($player),
    ];
  }

  public function actBlacksmith($force)
  {
    self::checkAction('actBlacksmith');
    if ($force <= 0 || $force > $this->argsBlacksmith()['max']) {
      throw new \BgaVisibleSystemException('Invalid weapon strength');
    }

    $player = Players::getCurrent();
    $dwarf = $this->getDwarf();
    $weapon = Dwarves::equipWeapon($dwarf, $force);
    Engine::insertAsChild([
      'action' => PAY,
      'args' => [
        'nb' => 1,
        'costs' => Utils::formatCost([ORE => $force]),
        'source' => clienttranslate('forging a weapon'),
      ],
    ]);
    Notifications::equipWeapon($player, $dwarf, $weapon);

    // Listeners for cards
    $eventData = [
      'weapon' => $force,
    ];
    $this->checkAfterListeners($player, $eventData);
    $this->resolveAction();
  }
}
