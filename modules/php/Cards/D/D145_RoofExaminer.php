<?php
namespace AGR\Cards\D;

class D145_RoofExaminer extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D145_RoofExaminer';
    $this->name = clienttranslate('Roof Examiner');
    $this->deck = 'D';
    $this->number = 145;
    $this->category = BUILDING_RESOURCE_PROVIDER;
    $this->desc = [
      clienttranslate(
        'When you play this card, if you have 1/2/3/4 major improvements, you immediately get 2/3/4/5 <REED>.'
      ),
    ];
    $this->players = '3+';
    $this->newSet = true;
  }
  
  public function onBuy($player)
  {
    $majors = $player->getCards(MAJOR, true)->count();
    $n = min($majors + 1, 5);
    
    if ($majors > 0) {
      return $this->gainNode([REED => $n]);
    }
  }
}
