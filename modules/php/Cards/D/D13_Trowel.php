<?php
namespace AGR\Cards\D;
use AGR\Helpers\Utils;

class D13_Trowel extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D13_Trowel';
    $this->name = clienttranslate('Trowel');
    $this->deck = 'D';
    $this->number = 13;
    $this->category = FARM_PLANNER;
    $this->desc = [
      clienttranslate(
        'At any time, you can renovate your house to stone. From a wooden house, this costs 1 <STONE>, 1 <REED>, and 1 <FOOD> per room. From a clay house, this costs 1 <STONE> per room.'
      ),
    ];
    $this->cost = [
      WOOD => 1,
    ];
    $this->newSet = true;
  }
  
  public function isListeningTo($event)
  {
    return $this->isAnytime($event);
  }

  public function onPlayerAtAnytime($player, $event)
  {
    $costs = [STONE => 1];
    
    if ($player->getRoomType() == 'roomWood') {
      $costs[REED] = 1;
      $costs[FOOD] = 1;
    }

    return [
      'action' => RENOVATION,
      'args' => ['costs' => Utils::formatCost($costs), 'toStone' => true],
    ];
  }
}
