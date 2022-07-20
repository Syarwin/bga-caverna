<?php
namespace CAV\Actions;

use CAV\Managers\Meeples;
use CAV\Managers\Players;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Helpers\Utils;
use CAV\Core\Stats;

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
    return $ignoreResources || $this->maximumForgeableWeapon($player) > 0;
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

    $dwarf = $this->getDwarf();
    var_dump($dwarf);
    die('NOT DONE YET');

    $player = Players::getCurrent();

    // Listeners for cards
    $eventData = [
      'roomType' => $roomType,
      'rooms' => $rooms,
      'oldRoomCount' => $oldRoomCount,
    ];
    $this->checkAfterListeners($player, $eventData);

    $this->resolveAction();
  }
}
