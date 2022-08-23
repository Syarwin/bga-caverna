<?php
namespace CAV\Buildings\Y;

class Y_BlacksmithingParlor extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_BlacksmithingParlor';
    $this->category = 'foodBonus';
    $this->name = clienttranslate('Blacksmithing Parlor');
    $this->desc = [clienttranslate('at anytime before scoring')];
    $this->tooltip = [
      clienttranslate('
      At any time before scoring, you can trade a set of 1 Ruby and 1 Ore for 2 Gold and 1 Food.'),
      clienttranslate('__(You may use the Blacksmithing parlor even after the final Harvest time.)__'),
    ];
    $this->cost = [ORE => 3];
    $this->vp = 2;
    $this->exchanges = [
      [
        'source' => $this->name,
        'flag' => $this->id,
        'from' => [
          RUBY => 1,
          ORE => 1,
        ],
        'to' => [
          GOLD => 2,
          FOOD => 1,
        ],
      ],
    ];
  }
}
