<?php
namespace CAV\Buildings\Y;

class Y_BroomChamber extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_BroomChamber';
    $this->category = 'bonus';
    $this->name = clienttranslate('Broom Chamber');
    $this->desc = [];
    $this->tooltip = [
      clienttranslate(
        'When scoring, the Broom chamber will be worth 5 Bonus points if you have 5 Dwarfs in play. It will be worth 10 Bonus points if you have 6 Dwarfs in play.'
      ),
      clienttranslate('__(It does not matter how many Dwarfs you had when you were building the Broom chamber.)__'),
    ];
    $this->cost = [WOOD => 1];
  }

  public function computeBonusScore()
  {
    $player = $this->getPlayer();
    $n = $player->countDwarfs();
    $bonus = 0;
    if ($n == 5) {
      $bonus = 5;
    } elseif ($n == 6) {
      $bonus = 10;
    }
    $this->addBonusScoringEntry($bonus);
  }
}
