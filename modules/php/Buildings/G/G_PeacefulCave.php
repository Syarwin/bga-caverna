<?php
namespace CAV\Buildings\G;

use CAV\Core\Engine;
use CAV\Core\Notifications;
use CAV\Managers\Meeples;

class G_PeacefulCave extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_PeacefulCave';
    $this->category = 'food';
    $this->name = clienttranslate('Peaceful Cave');
    $this->desc = [
      clienttranslate('you may trade your Weapons for <FOOD> at a 1:1 ration according to their strength'),
    ];
    $this->tooltip = [
      clienttranslate(
        'At any time, you can trade the Weapons of your Dwarfs for Food. You get a number of Food equal to the strength of the Weapon you trade in. You can trade multiple Weapons at the same time or at different points in time.'
      ),
      clienttranslate(
        '__(For instance, if you traded in a Weapon of strength 14, you would get 14 Food from the general supply. The Peaceful cave works well with the Prayer chamber.)__'
      ),
    ];
    $this->cost = [STONE => 2, WOOD => 2];
    $this->vp = 2;
  }

  public function isListeningTo($event)
  {
    return $this->isAnytime($event) && !$this->isFlagged() && $this->getPlayer()->hasArmedDwarfs();
  }

  public function onPlayerAtAnytime($player, $event)
  {
    $dwarfs = $this->getPlayer()->getAllDwarfs();
    $childs = [];
    foreach ($dwarfs as $dId => $dwarf) {
      if (($dwarf['weapon'] ?? 0) > 0) {
        $childs[] = [
          'action' => \SPECIAL_EFFECT,
          'args' => [
            'cardType' => $this->type,
            'method' => 'convertWeapon',
            'args' => [$dwarf['weapon'], $dId, $dwarf['weaponId']],
          ],
        ];
      }
    }
    return [
      'type' => NODE_SEQ,
      'childs' => [
        [
          'action' => \SPECIAL_EFFECT,
          'args' => [
            'cardType' => $this->type,
            'method' => 'flagCard',
          ],
        ],

        [
          'type' => NODE_OR,
          'optional' => true,
          'childs' => $childs,
          'args' => ['desc' => clienttranslate('Convert weapon into <FOOD>')],
        ],
        [
          'action' => \SPECIAL_EFFECT,
          'args' => [
            'cardType' => $this->type,
            'method' => 'unflagCard',
          ],
        ],
      ],
    ];
  }

  public function getConvertWeaponDescription($weapon, $dId, $weaponId)
  {
    return [
      'log' => clienttranslate('Convert weapon ${w} into ${w} <FOOD>'),
      'args' => ['w' => $weapon],
    ];
  }

  public function convertWeapon($weapon, $dId, $weaponId)
  {
    // remove weapon
    $dwarfs = $this->getPlayer()->getAllDwarfs();
    if (!isset($dwarfs[$dId]) || !isset($dwarfs[$dId]['weapon']) || $dwarfs[$dId]['weapon'] != $weapon) {
      throw new \BgaVisibleSystemException('This dwarf is not armed. Should not happen');
    }
    $m = Meeples::getMany([$weaponId]);
    Meeples::DB()->delete($weaponId);
    Notifications::destroyWeapon($this->getPlayer(), $m);

    // insert Food
    Engine::insertAsChild($this->gainNode([FOOD => $weapon]));
  }
}
