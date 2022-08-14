<?php
namespace CAV\Buildings\Y;

class Y_HuntingParlor extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_HuntingParlor';
    $this->category = 'foodBonus';
    $this->name = clienttranslate('Hunting Parlor');
    $this->tooltip = [
      clienttranslate('
      At any time before scoring, you can trade 2 Wild boars for 2 Gold and 2 Food __(instead of the usual 4 Food)__.'),
      clienttranslate(
        '__(You cannot trade a single Wild boar at the Hunting parlor. You may use the Hunting parlor even after the final Harvest time. You cannot use it in combination with the Slaughtering cave.)__'
      ),
    ];
    $this->cost = [WOOD => 2];
    $this->vp = 1;
    $this->exchanges = [
      [
        'source' => $this->name,
        'flag' => $this->id,
        'from' => [
          PIG => 2,
        ],
        'to' => [
          GOLD => 2,
          FOOD => 2,
        ],
      ],
    ];
  }
}
