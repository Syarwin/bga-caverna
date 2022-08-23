<?php
namespace CAV\Buildings\Y;

class Y_TreasureChamber extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_TreasureChamber';
    $this->category = 'bonus';
    $this->name = clienttranslate('Treasure Chamber');
    $this->desc = [clienttranslate('for each')];
    $this->tooltip = [
      clienttranslate('When scoring, the Treasure chamber will be worth 1 Bonus point for each Ruby you have.'),
      clienttranslate(
        '__(Consequently, your Rubies will be scored twice; in the “Rubies” category and then again in the “Bonus points for Parlors, Storages and Chambers” category.)__'
      ),
    ];
    $this->cost = [WOOD => 1, STONE => 1];
  }

  public function computeBonusScore()
  {
    $player = $this->getPlayer();
    $this->addBonusScoringEntry($player->countReserveResource(RUBY));
  }
}
