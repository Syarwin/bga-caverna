<?php
namespace CAV\ActionCards;
use CAV\Managers\Players;
use CAV\Helpers\Utils;

class ActionImitation5 extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionImitation5';
    $this->actionCardType = 'Imitation';
    $this->name = clienttranslate('Imitation');
    $this->tooltip = [
      clienttranslate('Use an Action space occupied by one of your opponents'),
      clienttranslate(
        'Special case: You may not imitate an Imitation action that is occupied by your opponent only to imitate another Action space that is occupied by one of your Dwarfs'
      ),
    ];
    $this->players = [5, 6, 7];
  }

  public function canBeCopied($player, $dwarf, $ignoreResources = false)
  {
    return false;
  }

  public function getFlow($player, $dwarf)
  {
    $playersMap = [5 => 4, 6 => 2, 7 => 1];
    return [
      'type' => NODE_SEQ,
      'childs' => [
        ['action' => PAY, 'args' => ['nb' => 1, 'costs' => Utils::formatCost([FOOD => $playersMap[Players::count()]])]],
        ['action' => IMITATE],
      ],
    ];
  }

  public function getDesc()
  {
    $playersMap = [5 => 4, 6 => 2, 7 => 1];
    $cost = ($playersMap[Players::count()] ?? 0);
    return [
      [
        'log' => clienttranslate('Pay ${n}<FOOD> to use an action space occupied by one of your opponent'),
        'args' => ['n' => $cost]
      ]
    ];
  }
}
