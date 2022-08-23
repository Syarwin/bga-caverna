<?php
namespace CAV\Buildings\Y;

class Y_OreStorage extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_OreStorage';
    $this->category = 'bonus';
    $this->name = clienttranslate('Ore Storage');
    $this->desc = [clienttranslate('for each')];
    $this->tooltip = [
      clienttranslate('When scoring, the Ore storage will be worth 1 Bonus point for every 2 Ore you have.'),
      clienttranslate('__(For instance, you get 1/2/3/… Bonus points for 2-3/4-5/6-7/… Ore, respectively.)__'),
    ];
    $this->cost = [WOOD => 1, STONE => 2];
  }

  public function computeBonusScore()
  {
    $player = $this->getPlayer();
    $ore = $player->countReserveResource(ORE);
    $this->addBonusScoringEntry(floor($ore / 2));
  }
}
