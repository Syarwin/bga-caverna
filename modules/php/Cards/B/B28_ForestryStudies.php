<?php
namespace AGR\Cards\B;
use AGR\Core\Notifications;
use AGR\Managers\Meeples;

class B28_ForestryStudies extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B28_ForestryStudies';
    $this->name = clienttranslate('Forestry Studies');
    $this->deck = 'B';
    $this->number = 28;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      clienttranslate(
        'Each time after you use the __Forest__ accumulation space, you can return 2 <WOOD> to that space to play 1 occupation without paying an occupation costs.'
      ),
    ];
    $this->cost = [
      FOOD => 2,
    ];
    $this->newSet = true;
  }
  
  public function isListeningTo($event)
  {
    return $this->isActionEvent($event, 'PlaceFarmer') && 
	  $event['actionCardType'] == 'Forest';
  }
  
  public function onPlayerAfterPlaceFarmer($player, $event)
  {
    return [
      'type' => NODE_SEQ,
      'optional' => true,
      'childs' => [
        $this->payNode([WOOD => 2], clienttranslate("Forestry Studies' effect")),
        [
          'action' => OCCUPATION,
          'args' => [
            'cost' => [],
            'source' => $this->name,
          ],		  
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
