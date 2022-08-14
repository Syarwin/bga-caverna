<?php
namespace CAV\Buildings\G;

class G_CookingCave extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_CookingCave';
    $this->category = 'food';
    $this->name = clienttranslate('Cooking Cave');
    $this->tooltip = [
      clienttranslate(
        'You will get 5 Food from the general supply (instead of 3) everytime you convert a set of 1 Grain and 1 Vegetable into Food'
      ),
    ];
    $this->cost = [STONE => 2];
    $this->vp = 2;
    $this->exchanges = [
      [
        'source' => $this->name,
        'flag' => $this->id,
        'from' => [
          GRAIN => 1,
          VEGETABLE => 1,
        ],
        'to' => [
          FOOD => 5,
        ],
      ],
    ];
  }
}
