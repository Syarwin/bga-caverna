<?php
namespace CAV\Buildings\G;

use CAV\Managers\Players;
use CAV\Managers\Buildings;

class G_Trader extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_Trader';
    $this->category = 'material';
    $this->name = clienttranslate('Trader');
    $this->desc = [clienttranslate('at anytime before scoring')];
    $this->tooltip = [
      clienttranslate(
        'At any time before scoring, you can buy 1 Wood, 1 Stone and 1 Ore from the general supply for a total of 2 Gold. (You can only buy the full set.)'
      ),
      clienttranslate(
        'A player who has already built the Spare part storage and decides to build the Trader as well must place the Trader on top of the Spare part storage, thus overbuilding the Spare part storage. (Do not return the Spare part storage to the general supply.)'
      ),
      clienttranslate(
        'He cannot use the Spare part storage any longer and does not get any points at the end of the game for it. The same applies if a player has already built the Trader and decides to build the Spare part storage. (In general, Furnishing tiles cannot be overbuilt.)'
      ),
    ];
    $this->cost = [WOOD => 1];
    $this->vp = 2;
    $this->exchanges = [
      [
        'source' => $this->name,
        'flag' => $this->id,
        'from' => [
          GOLD => 2,
        ],
        'to' => [
          WOOD => 1,
          STONE => 1,
          ORE => 1,
        ],
      ],
    ];
  }

  public function isListeningTo($event)
  {
    return $this->isActionEvent($event, 'Furnish') &&
      $event['buildingType'] == 'G_Trader' &&
      Players::get($event['pId'])->hasPlayedBuilding('Y_SparePartStorage');
  }

  public function onPlayerAfterFurnish($player, $event)
  {
    return [
      'action' => \SPECIAL_EFFECT,
      'args' => ['cardType' => $this->type, 'method' => 'removeBuilding', 'args' => []],
    ];
  }

  public function removeBuilding()
  {
    $b = Buildings::getFilteredQuery(null, null, 'Y_SparePartStorage')
      ->get()
      ->first();
    Buildings::DB()->delete($b->getId());
  }
}
