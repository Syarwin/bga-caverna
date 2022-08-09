<?php
namespace CAV\ActionCards;
use CAV\Helpers\Utils;

class ActionDonkeyFarming extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionDonkeyFarming';
    $this->name = clienttranslate('Donkey Farming');
    $this->desc = [clienttranslate('and then / or')];
    $this->tooltip = [
      clienttranslate(
        'Before taking all the Donkeys that have accumulated on this Action space, you may build a Small pasture, a Large pasture and a Stable.'
      ),
      clienttranslate('(Every round, 1 Donkey will be added to this Action space.)'),
      clienttranslate(
        'Pay 2 Wood for a Small pasture, 4 Wood for a Large pasture and 1 Stone for a Stable. You may build all three structures or only some of them (or none) but you may not build more than 1 Small and 1 Large pasture or more than 1 Stable with a single action.'
      ),
    ];

    $this->accumulation = [DONKEY => 1];
    $this->stage = 2;
  }

  protected function getFlow($player, $dwarf)
  {
    return [
      'type' => NODE_SEQ,
      'childs' => [
        [
          'type' => NODE_OR,
          'childs' => [
            [
              'action' => FENCING,
              'args' => ['type' => 'small', 'cost' => [WOOD => 2]],
            ],
            [
              'action' => FENCING,
              'args' => ['type' => 'large', 'cost' => [WOOD => 4]],
            ],
            [
              'action' => STABLES,
              'args' => ['max' => 1, 'costs' => Utils::formatCost([STONE => 1, 'max' => 1])],
            ],
          ],
        ],
        ['action' => COLLECT],
      ],
    ];
  }
}