<?php
namespace AGR\Cards\D;

class D125_ForestTrader extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D125_ForestTrader';
    $this->name = clienttranslate('Forest Trader');
    $this->deck = 'D';
    $this->number = 125;
    $this->category = BUILDING_RESOURCE_PROVIDER;
    $this->desc = [
      clienttranslate(
        'Each time you use a wood or clay accumulation space, you can also buy exactly 1 building resource. <WOOD>, <CLAY>, and <REED> cost 1 <FOOD> each; <STONE> costs 2 food.'
      ),
    ];
    $this->players = '1+';
    $this->newSet = true;
  }
  
  public function isListeningTo($event)
  {
    return $this->isBeforeCollectEvent($event, WOOD) ||
      $this->isBeforeCollectEvent($event, CLAY);
  }
  
  public function onPlayerPlaceFarmer($player, $event)
  {
    return [
      'type' => NODE_XOR,
      'optional' => true,
      'childs' => [
        $this->payGainNode([FOOD => 1], [WOOD => 1], null, false),
        $this->payGainNode([FOOD => 1], [CLAY => 1], null, false),
        $this->payGainNode([FOOD => 1], [REED => 1], null, false),
        $this->payGainNode([FOOD => 2], [STONE => 1], null, false),
      ]
    ];
  }
}
