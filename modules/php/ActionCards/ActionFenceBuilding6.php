<?php
namespace CAV\ActionCards;
use CAV\Helpers\Utils;

class ActionFenceBuilding6 extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionFenceBuilding6';
    $this->actionCardType = 'ActionFenceBuilding';
    $this->name = clienttranslate('Fence Building');
    $this->desc = [clienttranslate('and then / or')];
    $this->tooltip = [
      clienttranslate('Take all the Wood that has accumulated on this Action space'),
      clienttranslate(
        '(Every round, 1 wood will be added to this Action space. If empty, 2 woods will be added instead)'
      ),
      clienttranslate('Afterwards you may build a Small pasture or/and a Large pasture.'),
      clienttranslate('Pay 2 Wood for a Small pasture, 4 Wood for a Large pasture.'),
    ];

    $this->accumulation = [WOOD => [2, 1]];
    $this->players = [6, 7];
  }

  protected function getFlow($player, $dwarf)
  {
    return [
      'type' => NODE_SEQ,
      'childs' => [
        ['action' => COLLECT],
        [
          'type' => NODE_THEN_OR,
          'optional' => true,
          'childs' => [
            [
              'action' => FENCING,
              'args' => ['type' => 'small', 'cost' => [WOOD => 2]],
            ],
            [
              'action' => FENCING,
              'args' => ['type' => 'large', 'cost' => [WOOD => 4]],
            ],
          ],
        ],
      ],
    ];
  }
}
