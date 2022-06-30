<?php
namespace AGR\Cards\Major;
use AGR\Helpers\Utils;

class Major_ClayOven extends \AGR\Models\MajorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'Major_ClayOven';
    $this->number = 6;
    $this->name = clienttranslate('Clay Oven');
    $this->tooltip = [];
    $this->desc = [
      clienttranslate('[Action Bake]'),
      '<GRAIN> <ARROW-1X> 5<FOOD>',
      clienttranslate('[When you build it, you can Bake immediatly]'),
    ];

    $this->cost = [CLAY => 3, STONE => 1];
    $this->vp = 2;
    $this->exchanges = [Utils::formatExchange([GRAIN => [FOOD => 5], 'max' => 1], $this->name, [BREAD])];
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
