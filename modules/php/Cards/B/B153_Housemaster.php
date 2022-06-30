<?php
namespace AGR\Cards\B;

class B153_Housemaster extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B153_Housemaster';
    $this->name = clienttranslate('Housemaster');
    $this->deck = 'B';
    $this->number = 153;
    $this->category = POINTS_PROVIDER;
    $this->desc = [
      clienttranslate(
        'During scoring, total the base point values of your major improvements. The smallest value counts double. If the total is at least 5/7/9/11, you get 1/2/3/4 bonus <SCORE>.'
      ),
    ];
    $this->players = '4+';
    $this->newSet = true;
  }
  
  public function computeBonusScore()
  {
    $bonus = 0;
    $player = $this->getPlayer();

    $points = [];
    foreach ($player->getCards(MAJOR, true) as $major) {
      array_push($points, $major->getScore());
    }
    
    if ($points == []) {
      return null;
    }
    
    $total = array_sum($points) + min($points);

    if ($total >= 5) {$bonus = 1;}
    if ($total >= 7) {$bonus = 2;}
    if ($total >= 9) {$bonus = 3;}
    if ($total >= 11) {$bonus = 4;}
    
    if ($bonus > 0) {
      $this->addBonusScoringEntry($bonus);
    }
  } 
}
