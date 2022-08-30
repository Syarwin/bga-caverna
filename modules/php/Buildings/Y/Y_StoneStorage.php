<?php
namespace CAV\Buildings\Y;

class Y_StoneStorage extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_StoneStorage';
    $this->category = 'bonus';
    $this->name = clienttranslate('Stone Storage');
    $this->desc = [clienttranslate('for each')];
    $this->tooltip = [
      clienttranslate('
      When scoring, the Stone storage will be worth 1 Bonus point for each Stone you have.'),
    ];
    $this->cost = [WOOD => 3, ORE => 1];
    $this->beginner = true;
  }

  public function computeBonusScore()
  {
    $player = $this->getPlayer();
    $this->addBonusScoringEntry($player->countReserveResource(STONE));
  }
}
