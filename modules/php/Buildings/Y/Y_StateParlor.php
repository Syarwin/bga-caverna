<?php

namespace CAV\Buildings\Y;

use CAV\Managers\Buildings;

class Y_StateParlor extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_StateParlor';
    $this->category = 'foodBonus';
    $this->name = clienttranslate('State Parlor');
    $this->desc = [
      clienttranslate('Receive immediately for each adjacent dwelling +2<FOOD>'),
      clienttranslate('when scoring'),
    ];
    $this->tooltip = [
      clienttranslate(
        'When building the State parlor, immediately (and only once) get 2 Food from the general supply for each Dwelling that is __(horizontally or vertically)__ adjacent to the State parlor.'
      ),
      clienttranslate(
        'When scoring, you will get 4 Bonus points for each Dwelling that is (horizontally or vertically) adjacent to the State parlor (i.e. at most 16 Bonus points). The entry-level room of your cave is also considered a Dwelling'
      ),
    ];
    $this->cost = [GOLD => 5, STONE => 3];
  }

  protected function onBuy($player, $eventData)
  {
    return $this->gainNode([FOOD => 2 * $this->calculateAdjacentDwellings()]);
  }

  protected function calculateAdjacentDwellings()
  {
    $adj = 0;

    foreach ($this->getPlayer()
        ->board()
        ->getAdjacentTiles($this->x, $this->y)
      as $tile) {
      $b = Buildings::getFilteredQuery($this->getPId(), null, null)
        ->where([['x', $tile['x']], ['y', $tile['y']]])
        ->get(true);

      if (!is_null($b) && $b->isConsideredDwelling()) {
        $adj++;
      }
    }
    return $adj;
  }

  public function computeBonusScore()
  {
    $this->addBonusScoringEntry($this->calculateAdjacentDwellings() * 4);
  }
}
