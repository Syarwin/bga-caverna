<?php
namespace CAV\Actions;
use CAV\Managers\Meeples;
use CAV\Managers\Players;
use CAV\Managers\Dwarfs;
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
    if (!isset($args['dwarfs'])) {
      $dwarfs = $player->getAllDwarfs();
    } else {
      $dwarfs = $args['dwarfs'];
    }
    $increase = $args['increase'] ?? 1;

    $upgradedDwarfs = [];
    foreach ($dwarfs as $dwarf) {
      // we take the last version of the dwarf as the one in args may be updated
      $nDwarf = Dwarfs::get($dwarf['id']);
      if (isset($nDwarf['weapon']) && $nDwarf['weapon'] > 0) {
        Dwarfs::upgradeWeapon($nDwarf, $increase);
        $upgradedDwarfs[] = $dwarf['id'];
      }
    }
    $source = $args['source'] ?? null;
    if ($source == 'expedition') {
      $source = clienttranslate('Expedition\'s loot');
    }

    if (!empty($upgradedDwarfs)) {
      Notifications::upgradeWeapon($player, Dwarfs::getMany($upgradedDwarfs), $source);
    }

    $this->resolveAction();
  }

  public function isAutomatic($player = null)
  {
    return true;
  }
}
