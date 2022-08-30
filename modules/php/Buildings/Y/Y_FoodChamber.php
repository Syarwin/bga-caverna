<?php
namespace CAV\Buildings\Y;

class Y_FoodChamber extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_FoodChamber';
    $this->category = 'bonus';
    $this->name = clienttranslate('Food Chamber');
    $this->desc = [clienttranslate('for each')];
    $this->tooltip = [
      clienttranslate(
        'When scoring, the Food chamber will be worth 2 Bonus points for each set of 1 Grain and 1 Vegetable that you have in your supply and/or left on your Fields'
      ),
      clienttranslate('__(You will get the usual ½ Gold point per Grain and 1 Gold point per Vegetable regardless.)__'),
    ];
    $this->cost = [WOOD => 2, \VEGETABLE => 2];
    $this->beginner = true;
  }

  public function computeBonusScore()
  {
    $player = $this->getPlayer();
    $vegetables = $player->countReserveAndGrowingResource(VEGETABLE);
    $grains = $player->countReserveAndGrowingResource(GRAIN);
    $sets = min($vegetables, $grains);
    $bonus = 2 * $sets;
    $this->addBonusScoringEntry($bonus);
  }
}
