<?php
namespace AGR\Cards\D;
use AGR\Managers\Meeples;
use AGR\Core\Notifications;

class D138_PetLover extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D138_PetLover';
    $this->name = clienttranslate('Pet Lover');
    $this->deck = 'D';
    $this->number = 138;
    $this->category = GOODS_PROVIDER;
    $this->desc = [
      clienttranslate(
        'Each time you use an accumulation space providing exactly 1 animal, you can leave it on the space and get one from the general supply instead, as well as 3 <FOOD> and 1 <GRAIN>.'
      ),
    ];
    $this->players = '3+';
  }

  public function isListeningTo($event)
  {
    return $this->isCollectEvent($event, null, true) &&
      count($event['meeples']) == 1 &&
      in_array($event['meeples'][0]['type'], ANIMALS);
  }

  public function onPlayerImmediatelyAfterCollect($player, $event)
  {
    return [
      'type' => \NODE_SEQ,
      'optional' => true,
      'childs' => [
        [
          'action' => SPECIAL_EFFECT,
          'args' => [
            'cardId' => $this->id,
            'method' => 'putOneBack',
            'args' => [$event['actionCardId'], $event['meeples'][0]['type']],
          ],
        ],
        $this->gainNode([FOOD => 3, GRAIN => 1]),
      ],
    ];
  }

  public function putOneBack($actionCardId, $meepleType)
  {
    $resourceIds = Meeples::createResourceOnCard($meepleType, $actionCardId, 1);
    Notifications::accumulate(Meeples::getMany($resourceIds), true);
  }

  public function getPutOneBackDescription()
  {
    return clienttranslate('Add 1 animal back');
  }
}
