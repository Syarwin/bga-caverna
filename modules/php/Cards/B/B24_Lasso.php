<?php
namespace AGR\Cards\B;
use AGR\Core\Engine;

class B24_Lasso extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B24_Lasso';
    $this->name = clienttranslate('Lasso');
    $this->deck = 'B';
    $this->number = 24;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      clienttranslate(
        'You can place exactly two people immediately after one another if at least one of them uses the __Sheep Market__, __Pig Market__, or __Cattle Market__ accumulation space.'
      ),
    ];
    $this->cost = [
      REED => 1,
    ];
  }

  public function isListeningTo($event)
  {
    if ($this->isActionEvent($event, 'PlaceFarmer')) {
      return !$this->isFlagged();
    }
    return false;
  }

  public function onPlayerAfterPlaceFarmer($player, $event)
  {
    $cards = ['ActionSheepMarket', 'ActionPigMarket', 'ActionCattleMarket'];
    
    return [
      'type' => NODE_SEQ,
      'optional' => true,
      'childs' => [
        $this->flagCardNode(),
        [
          'action' => PLACE_FARMER,
          'pId' => $player->getId(),
          'args' => [
            'constraints' => in_array($event['actionCardId'], $cards) ? null : $cards,
          ],
          'source' => $this->name,
        ],
        $this->unflagCardNode(),
      ],
    ];
  }
}
