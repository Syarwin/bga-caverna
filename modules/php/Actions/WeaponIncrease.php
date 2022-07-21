<?php
namespace CAV\Actions;
use CAV\Managers\Meeples;
use CAV\Managers\Players;
use CAV\Managers\Dwarves;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Core\Stats;
use CAV\Helpers\Utils;

class WeaponIncrease extends \CAV\Models\Action
{
  public function getState()
  {
    return ST_WEAPON_INCREASE;
  }

  public function stWeaponIncrease()
  {
    $player = Players::getActive();
    $args = $this->getCtxArgs();
    if (!isset($args['dwarves'])) {
      $dwarves = $player->getAllDwarves();
    } else {
      $dwarves = $args['dwarves'];
    }
    $increase = $args['increase'] ?? 1;

    $upgradedDwarves = [];
    foreach ($dwarves as $dwarf) {
      // we take the last version of the dwarf as the one in args may be updated
      $nDwarf = Dwarves::get($dwarf['id']);
      if (isset($nDwarf['weapon']) && $nDwarf['weapon'] > 0) {
        Dwarves::upgradeWeapon($nDwarf, $increase);
        $upgradedDwarves[] = $dwarf['id'];
      }
    }
    $source = $args['source'] ?? null;
    if ($source == 'expedition') {
      $source = clienttranslate('Expedition\'s loot');
    }

    if (!empty($upgradedDwarves)) {
      Notifications::upgradeWeapon($player, Dwarves::getMany($upgradedDwarves), $source);
    }

    $this->resolveAction();
  }

  public function isAutomatic($player = null)
  {
    return true;
  }
}
