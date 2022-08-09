<?php
namespace CAV\ActionCards;

class ActionWeeklyMarket extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionWeeklyMarket';
    $this->name = clienttranslate('Weekly Market');
    $this->tooltip = [
      clienttranslate('Take 4 gold from the general supply.'),
      clienttranslate('Additionally you may (but do not need to) buy goods with gold (once per item)'),
      clienttranslate('Any building material, Sheep or Donkey costs 1 Gold.'),
      clienttranslate('A Wild boar or Dog costs 2 Gold.'),
      clienttranslate('Cattle costs 3 Gold. Grain costs 1 Gold and a Vegetable costs 2 Gold.'),
    ];
    $this->desc = [clienttranslate('then')];
    $this->players = [5, 6, 7];
  }

  public function getFlow($player, $dwarf)
  {
    return [
      'type' => NODE_SEQ,
      'childs' => [
        ['action' => GAIN, 'args' => [GOLD => 4]],
        [
          'type' => NODE_OR,
          'optional' => true,
          'childs' => [
            $this->payGainNode([GOLD => 2], [DOG => 1]),
            $this->payGainNode([GOLD => 1], [SHEEP => 1]),
            $this->payGainNode([GOLD => 1], [\DONKEY => 1]),
            $this->payGainNode([GOLD => 2], [PIG => 1]),
            $this->payGainNode([GOLD => 3], [CATTLE => 1]),
            $this->payGainNode([GOLD => 1], [WOOD => 1]),
            $this->payGainNode([GOLD => 1], [STONE => 1]),
            $this->payGainNode([GOLD => 1], [ORE => 1]),
            $this->payGainNode([GOLD => 1], [GRAIN => 1]),
            $this->payGainNode([GOLD => 2], [VEGETABLE => 1]),
          ],
        ],
      ],
    ];
  }
}
