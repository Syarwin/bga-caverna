<?php
namespace AGR\Cards\Major;
use AGR\Helpers\Utils;

class Major_StoneOven extends \AGR\Models\MajorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'Major_StoneOven';
    $this->number = 7;
    $this->name = clienttranslate('Stone Oven');
    $this->tooltip = [];
    $this->desc = [
      clienttranslate('[Action Bake]'),
      '<GRAIN> <ARROW-2X> 4<FOOD>',
      clienttranslate('[When you build it, you can Bake immediatly]'),
    ];

    $this->cost = [CLAY => 1, STONE => 3];
    $this->vp = 3;
    $this->exchanges = [Utils::formatExchange([GRAIN => [FOOD => 4], 'max' => 2], $this->name, [BREAD])];
  }

  protected function onBuy($player)
  {
    // trigger bake Action
    return [
      'action' => EXCHANGE,
      'optional' => true,
      'args' => [
        'trigger' => BREAD,
      ],
    ];
  }
}
