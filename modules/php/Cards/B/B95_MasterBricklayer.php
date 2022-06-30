<?php
namespace AGR\Cards\B;

class B95_MasterBricklayer extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B95_MasterBricklayer';
    $this->name = clienttranslate('Master Bricklayer');
    $this->deck = 'B';
    $this->number = 95;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      clienttranslate(
        'Each time you build a major improvement, reduce the <STONE> cost by the number of rooms you have built onto your initial house.'
      ),
    ];
    $this->players = '1+';
  }

  public function onPlayerComputeCardCosts($player, &$args)
  {
    if ($args['card']->getType() != MAJOR) {
      return;
    }

    $nbNewRooms = $player->countRooms() - 2;

    foreach ($args['costs']['trades'] as &$trade) {
      if (isset($trade[STONE])) {
        $trade[STONE] -= $nbNewRooms;
        $trade['sources'][] = $this->id;
        if ($trade[STONE] <= 0) {
          unset($trade[STONE]);
        }
      }
    }
  }
}
