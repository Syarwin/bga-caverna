<?php
namespace CAV\Buildings\Y;

class Y_BeerParlor extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_BeerParlor';
    $this->category = 'foodBonus';
    $this->name = clienttranslate('Beer Parlor');
    $this->tooltip = [
      clienttranslate('
      At any time before scoring, you can trade 2 Grain from your personal supply for 3 Gold or 4 Food.'),
      clienttranslate(
        '__(2 Grain are thus worth 2 more Food. You cannot trade a single Grain at the Beer parlor. You may use the Beer parlor even after the final Harvest time.)__'
      ),
    ];
    $this->cost = [WOOD => 2];
    $this->vp = 3;
    $this->exchanges = [
      [
        'source' => $this->name,
        'flag' => $this->id,
        'from' => [
          GRAIN => 2,
        ],
        'to' => [
          GOLD => 3,
        ],
      ],
      [
        'source' => $this->name,
        'flag' => $this->id,
        'from' => [
          GRAIN => 2,
        ],
        'to' => [
          FOOD => 4,
        ],
      ],
    ];
  }
}
