<?php
namespace CAV\ActionCards;
use CAV\Helpers\Utils;

class ActionOreTrading extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionOreTrading';
    $this->name = clienttranslate('Ore Trading');
    $this->tooltip = [
      clienttranslate(
        'You may trade 2 Ore for 2 Gold and 1 Food with the general supply. You may do this up to 3 times.'
      ),
    ];

    $this->stage = 4;
  }

  protected function getFlow($player, $dwarf)
  {
    return [
      'type' => NODE_XOR,
      'childs' => [
        [
          'type' => NODE_SEQ,
          'childs' => [
            [
              'action' => PAY,
              'args' => ['nb' => 1, 'costs' => Utils::formatCost([ORE => 2])],
            ],
            [
              'action' => GAIN,
              'args' => [GOLD => 2, FOOD => 1],
            ],
          ],
        ],
        [
          'type' => NODE_SEQ,
          'childs' => [
            [
              'action' => PAY,
              'args' => ['nb' => 1, 'costs' => Utils::formatCost([ORE => 4])],
            ],
            [
              'action' => GAIN,
              'args' => [GOLD => 4, FOOD => 2],
            ],
          ],
        ],
        [
          'type' => NODE_SEQ,
          'childs' => [
            [
              'action' => PAY,
              'args' => ['nb' => 1, 'costs' => Utils::formatCost([ORE => 6])],
            ],
            [
              'action' => GAIN,
              'args' => [GOLD => 6, FOOD => 3],
            ],
          ],
        ],
      ],
    ];
  }
}
