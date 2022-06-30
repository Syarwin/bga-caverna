<?php
namespace AGR\Cards\Major;
use AGR\Helpers\Utils;

class Major_Fireplace2 extends \AGR\Models\MajorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'Major_Fireplace2';
    $this->number = 2;
    $this->name = clienttranslate('Fireplace');
    $this->tooltip = [];
    $this->desc = [
      clienttranslate('[Anytime]'),
      '<VEGETABLE> <ARROW> 2<FOOD>      <PIG> <ARROW> 2<FOOD>',
      '<SHEEP> <ARROW> 2<FOOD>      <CATTLE> <ARROW> 3<FOOD>',
      clienttranslate('[Action Bake]'),
      '<GRAIN> <ARROW> 2<FOOD>',
    ];

    $this->cost = [CLAY => 3];
    $this->vp = 1;
    $this->exchanges = [
      Utils::formatExchange([VEGETABLE => [FOOD => 2]], $this->name),
      Utils::formatExchange([PIG => [FOOD => 2]], $this->name),
      Utils::formatExchange([SHEEP => [FOOD => 2]], $this->name),
      Utils::formatExchange([CATTLE => [FOOD => 3]], $this->name),
      Utils::formatExchange([GRAIN => [FOOD => 2]], $this->name, [BREAD]),
    ];
  }
}
