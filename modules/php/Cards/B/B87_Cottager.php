<?php
namespace AGR\Cards\B;
use AGR\Managers\Actions;

class B87_Cottager extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B87_Cottager';
    $this->name = clienttranslate('Cottager');
    $this->deck = 'B';
    $this->number = 87;
    $this->category = FARM_PLANNER;
    $this->desc = [
      clienttranslate(
        'Each time you use the __Day Laborer__ action space, you can also either build exactly 1 room or renovate your house. Either way, you have to pay the cost.'
      ),
    ];
    $this->players = '1+';
  }

  public function isListeningTo($event)
  {
    return $this->isActionCardEvent($event, 'DayLaborer');
  }

  public function onPlayerPlaceFarmer($player, $args)
  {
    $childs = [];
    if (Actions::get(CONSTRUCT)->isDoable($player, true)) {
      $childs[] = [
        'action' => CONSTRUCT,
        'args' => [
          'max' => 1,
        ],
      ];
    }

    if (Actions::get(RENOVATION)->isDoable($player, true)) {
      $childs[] = [
        'action' => RENOVATION,
      ];
    }

    if (!empty($childs)) {
      return [
        'type' => NODE_XOR,
        'actionId' => $this->id,
        'optional' => true,
        'childs' => $childs,
      ];
    }
  }
}
