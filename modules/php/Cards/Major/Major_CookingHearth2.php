<?php
namespace AGR\Cards\Major;
use AGR\Helpers\Utils;

class Major_CookingHearth2 extends \AGR\Models\MajorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'Major_CookingHearth2';
    $this->number = 4;
    $this->name = clienttranslate('Cooking Hearth');
    $this->tooltip = [];
    $this->desc = [
      clienttranslate('[Anytime]'),
      '<VEGETABLE> <ARROW> 3<FOOD>      <PIG> <ARROW> 3<FOOD>',
      '<SHEEP> <ARROW> 2<FOOD>      <CATTLE> <ARROW> 4<FOOD>',
      clienttranslate('[Action Bake]'),
      '<GRAIN> <ARROW> 3<FOOD>',
    ];
    $this->costText = clienttranslate('Return Fireplace or');

    $this->cost = [CLAY => 5];
    $this->returnCards = ['Major_Fireplace1', 'Major_Fireplace2'];
    $this->vp = 1;

    $this->exchanges = [
      Utils::formatExchange([VEGETABLE => [FOOD => 3]], $this->name),
      Utils::formatExchange([PIG => [FOOD => 3]], $this->name),
      Utils::formatExchange([SHEEP => [FOOD => 2]], $this->name),
      Utils::formatExchange([CATTLE => [FOOD => 4]], $this->name),
      Utils::formatExchange([GRAIN => [FOOD => 3]], $this->name, [BREAD]),
    ];
  }
}
