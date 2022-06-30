<?php
namespace AGR\Cards\B;
use AGR\Managers\Meeples;
use AGR\Core\Notifications;
use AGR\Core\Stats;

class B83_MuddyPuddles extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B83_MuddyPuddles';
    $this->name = clienttranslate('Muddy Puddles');
    $this->deck = 'B';
    $this->number = 83;
    $this->category = LIVESTOCK_PROVIDER;
    $this->desc = [
      clienttranslate(
        'Pile (from bottom to top) 1 <PIG>, 1 <FOOD>, 1 <CATTLE>, 1 <FOOD>, and 1 <SHEEP> on this card. At any time, you can pay 1 <CLAY> to take the top good.'
      ),
    ];
    $this->cost = [
      CLAY => 2,
    ];
    $this->holder = true;
    $this->newSet = true;
  }
  
  public function onBuy($player)
  {
    $types = [PIG, FOOD, CATTLE, FOOD, SHEEP];

    $created = [];
    foreach ($types as $i => $type) {
      $created = array_merge(
        $created,
        Meeples::createResourceInLocation($type, $this->id, $player->getId(), null, null, 1, $i)
      );
    }

    Notifications::accumulate(Meeples::getMany($created), true);
  }
  
  public function isListeningTo($event)
  {
    return $this->isAnytime($event) && $this->getNextResource() != null;
  }

  public function onPlayerAtAnytime($player, $event)
  {
    $meeple = $this->getNextResource();  
      
    return [
      'type' => NODE_SEQ,
      'childs' => [
        $this->payNode([CLAY => 1]),
        $this->receiveNode($meeple['id']),
      ],
    ];
  }
}
