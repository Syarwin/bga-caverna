<?php
namespace CAV\Buildings\Y;

class Y_SuppliesStorage extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_SuppliesStorage';
    $this->category = 'bonus';
    $this->name = clienttranslate('Supplies Storage');
    $this->tooltip = [
      clienttranslate(
        'When scoring, the Supplies storage will be worth 8 Bonus points if all of your Dwarfs that are in play have a Weapon.'
      ),
      clienttranslate(
        '__(The Weapon strength does not matter. The fewer Dwarfs you have, the easier it is to accomplish that. The Supplies storage can be combined with the Weapon storage.)__'
      ),
    ];
    $this->cost = [WOOD => 3, FOOD => 1];
  }

  public function computeBonusScore()
  {
    $player = $this->getPlayer();
    $dwarfs = $player->getAllDwarfs();
    foreach ($dwarfs as $d) {
      if (($d['weapon'] ?? 0) == 0) {
        return;
      }
    }

    $this->addBonusScoringEntry(8);
  }
}
