<?php
namespace AGR\Cards\D;
use AGR\Helpers\Utils;

class D144_WaterWorker extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D144_WaterWorker';
    $this->name = clienttranslate('Water Worker');
    $this->deck = 'D';
    $this->number = 144;
    $this->category = BUILDING_RESOURCE_PROVIDER;
    $this->desc = [
      clienttranslate(
        'Each time after you use any of the __Fishing__, __Reed Bank__, __Day Laborer__ spaces or the action space of round 4, you get 1 additional <REED>.'
      ),
    ];
    $this->players = '3+';
  }

  public function isListeningTo($event)
  {
    if (!$this->isActionEvent($event, 'PlaceFarmer')) {
      return false;
    }

    $turn4 = false;
    $cardId = $event['actionCardId'] ?? null;
    if ($cardId != null) {
      $turn = Utils::getActionCard($cardId)->getTurn();
      if ($turn == 4) {
        $turn4 = $event['actionCardType'] == substr($cardId, 6);
      }
    }
    return $turn4 ||
      $event['actionCardType'] == 'DayLaborer' ||
      $event['actionCardType'] == 'ReedBank' ||
      $event['actionCardType'] == 'Fishing';
  }

  public function onPlayerAfterPlaceFarmer($player, $args)
  {
    return [
      'action' => GAIN,
      'pId' => $player->getId(),
      'args' => [REED => 1],
      'source' => $this->name,
    ];
  }
}
