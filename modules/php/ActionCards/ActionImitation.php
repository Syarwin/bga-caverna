<?php
namespace CAV\ActionCards;
use CAV\Managers\Players;
use CAV\Helpers\Utils;

class ActionImitation extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionImitation';
    $this->name = clienttranslate('Imitation');
    $this->tooltip = [
      clienttranslate('Use an Action space occupied by one of your opponents'),
      clienttranslate(
        'Special case: You may not imitate an Imitation action that is occupied by your opponent only to imitate another Action space that is occupied by one of your Dwarfs'
      ),
    ];
    $this->players = [3, 4, 5, 6, 7];
  }

  public function getFlow($player, $dwarf)
  {
    $playersMap = [3 => 4, 4 => 2, 5 => 2, 6 => 1, 7 => 0];
    return [
      'type' => NODE_SEQ,
      'childs' => [
        [
          'action' => PAY,
          'args' => ['nb' => 1, 'costs' => Utils::formatCost([FOOD => $playersMap[Players::count()] ?? 0])],
        ],
        ['action' => IMITATE],
      ],
    ];
  }

  public function getDesc()
  {
    $playersMap = [3 => 4, 4 => 2, 5 => 2, 6 => 1, 7 => 0];
    return $playersMap[Players::count()] ?? 0 . ' <FOOD>';
  }
}
