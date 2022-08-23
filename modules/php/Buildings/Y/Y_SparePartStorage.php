<?php
namespace CAV\Buildings\Y;

use CAV\Managers\Players;
use CAV\Managers\Buildings;

class Y_SparePartStorage extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_SparePartStorage';
    $this->category = 'bonus';
    $this->name = clienttranslate('Spare Part Storage');
    $this->tooltip = [
      clienttranslate(
        'At any time before scoring, you can trade sets of 1 Wood, 1 Stone and 1 Ore for 2 Gold per set.'
      ),
      clienttranslate('__(You may use the Spare part storage even after the final Harvest time.)__'),
      clienttranslate(
        'A player who has already built the Trader and decides to build the Spare part storage as well must place the Spare part storage on top of the Trader, thus overbuilding the Trader. (Do not return the Trader to the general supply.) He cannot use the Trader any longer and does not get any points at the end of the game for it. The same applies if a player has already built the Spare part storage and decides to build the Trader. (In general, Furnishing tiles cannot be overbuilt.)'
      ),
    ];
    $this->cost = [WOOD => 2];
    $this->exchanges = [
      [
        'source' => $this->name,
        'flag' => $this->id,
        'from' => [
          WOOD => 1,
          STONE => 1,
          ORE => 1,
        ],
        'to' => [
          GOLD => 2,
        ],
      ],
    ];
  }

  public function isListeningTo($event)
  {
    return $this->isActionEvent($event, 'Furnish') &&
      $event['buildingType'] == 'Y_SparePartStorage' &&
      Players::get($event['pId'])->hasPlayedBuilding('G_Trader');
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
    $b = Buildings::getFilteredQuery(null, null, 'G_Trader')
      ->get()
      ->first();
    Buildings::DB()->delete($b->getId());
  }
}
