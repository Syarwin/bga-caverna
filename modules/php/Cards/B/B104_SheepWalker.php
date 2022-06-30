<?php
namespace AGR\Cards\B;
use AGR\Core\Globals;
use AGR\Helpers\Utils;

class B104_SheepWalker extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B104_SheepWalker';
    $this->name = clienttranslate('Sheep Walker');
    $this->deck = 'B';
    $this->number = 104;
    $this->category = GOODS_PROVIDER;
    $this->desc = [
      clienttranslate(
        'At any time, you can exchange 1 <SHEEP> on your farmyard for either 1 <PIG>, 1 <VEGETABLE>, or 1 <STONE>.'
      ),
    ];
    $this->players = '1+';
  }

  public function getExchanges()
  {
    // No pending animals
    $reserve = $this->getPlayer()
      ->getAnimals('reserve')
      ->count();
    $sheeps = $this->getPlayer()->getExchangeResources()[SHEEP];

    $harvest = Globals::isHarvest() && Globals::getTurn() == 14? [HARVEST] : null;

    return $reserve == 0
      ? [
        Utils::formatExchange([SHEEP => [PIG => 1]], $this->name, $harvest),
        Utils::formatExchange([SHEEP => [VEGETABLE => 1]], $this->name, $harvest),
        Utils::formatExchange([SHEEP => [STONE => 1]], $this->name, $harvest),
      ]
      : [];
  }
  
  public function enforceReorganizeOnLastHarvest()
  {
    $sheep = $this->getPlayer()->countAnimalsOnBoard()[SHEEP];
    return $sheep > 0;
  }  
}
