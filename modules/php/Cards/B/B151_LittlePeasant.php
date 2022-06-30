<?php
namespace AGR\Cards\B;
use AGR\Managers\ActionCards;

class B151_LittlePeasant extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B151_LittlePeasant';
    $this->name = clienttranslate('Little Peasant');
    $this->deck = 'B';
    $this->number = 151;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      clienttranslate(
        'You immediately get 1 <STONE>. As long as you live in a wooden house with exactly 2 rooms, actions spaces—excluding Meeting Place—are not considered occupied for you.'
      ),
    ];
    $this->players = '4+';
    $this->newSet = true;
  }
  
  public function onBuy($player)
  {
    return $this->gainNode([STONE => 1]);
  }  
  
  public function onPlayerComputeArgsPlaceFarmer($player, &$args)
  {
    $houseCheck = false;
    if ($player->countRooms() == 2 &&  $player->getRoomType() == 'roomWood') {
      $houseCheck = true;
    }
      
    $cards = ActionCards::getVisible($player);
    $args['actionArgs']['cards'] = $cards
      ->filter(function ($card) use ($player, $houseCheck) {
        return $card->canBePlayed($player, ($houseCheck && $card->getActionCardType() != 'MeetingPlace') ? 'dummy' : null);
      })
      ->getIds();    
  }
}
