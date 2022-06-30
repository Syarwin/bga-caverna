<?php
namespace AGR\Cards\B;
use AGR\Core\Notifications;
use AGR\Managers\Meeples;

class B17_ForestPlow extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B17_ForestPlow';
    $this->name = clienttranslate('Forest Plow');
    $this->deck = 'B';
    $this->number = 17;
    $this->category = FARM_PLANNER;
    $this->desc = [
      clienttranslate(
        'Each time after you use a wood accumulation space, you can pay 2 <WOOD> to plow 1 field. Place the paid <WOOD> on the accumulation space (for the next visitor).'
      ),
    ];
    $this->cost = [
      WOOD => 1,
    ];
    $this->newSet = true;
  }

  public function isListeningTo($event)
  {
    return $this->isCollectEvent($event, WOOD);
  }

  public function onPlayerAfterCollect($player, $event)
  {
    return [
      'type' => NODE_SEQ,
      'optional' => true,
      'childs' => [
        $this->payNode([WOOD => 2], clienttranslate("Forest Plow's effect")),
        [
          'action' => PLOW,
          'source' => $this->name,
        ],
        [
          'action' => SPECIAL_EFFECT,
          'args' => [
            'cardId' => $this->id,
            'method' => 'putWoodBack',
            'args' => [$event['actionCardId']],
          ]
        ],
      ]
    ];
  }

  public function putWoodBack($actionCardId)
  {
    $resourceIds = Meeples::createResourceOnCard(WOOD, $actionCardId, 2);
    Notifications::accumulate(Meeples::getMany($resourceIds), true);
  }
}
