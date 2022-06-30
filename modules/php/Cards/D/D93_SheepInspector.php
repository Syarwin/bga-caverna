<?php
namespace AGR\Cards\D;

class D93_SheepInspector extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D93_SheepInspector';
    $this->name = ('Sheep Inspector');
    $this->deck = 'D';
    $this->number = 93;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      (
        'Once per work phase, after you complete a person action, you can pay 1 sheep and 2 food to return another person you placed home.'
      ),
    ];
    $this->players = '1+';
    $this->implemented = false;
  }
}
