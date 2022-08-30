<?php
namespace CAV\Buildings\Y;

class Y_WeaponStorage extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_WeaponStorage';
    $this->category = 'bonus';
    $this->name = clienttranslate('Weapon Storage');
    $this->desc = [clienttranslate('for each')];
    $this->tooltip = [
      clienttranslate(
        'When scoring, the Weapon storage will be worth 3 Bonus points for each armed Dwarf you have __(regardless of its Weapon strength)__.'
      ),
    ];
    $this->cost = [WOOD => 3, STONE => 2];
    $this->beginner = true;
  }

  public function computeBonusScore()
  {
    $player = $this->getPlayer();
    $dwarfs = $player->getAllDwarfs();
    $nArmedDwafs = 0;
    foreach ($dwarfs as $d) {
      if (($d['weapon'] ?? 0) > 0) {
        $nArmedDwafs++;
      }
    }

    $this->addBonusScoringEntry($nArmedDwafs * 3);
  }
}
