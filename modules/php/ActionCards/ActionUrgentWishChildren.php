<?php
namespace CAV\ActionCards;

class ActionUrgentWishChildren extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionUrgentWishChildren';
    $this->name = clienttranslate('Urgent Wish children');
    $this->desc = [clienttranslate('Either'), clienttranslate('or')];

    $this->tooltip = [
      clienttranslate('You may *either* build a Dwelling on an empty Cavern by paying its building costs or take 3 Gold from the general supply. If you do the
former, you may then carry out a Family growth action. __(You may not grow your family on this Action space unless you use the first action to build a Dwelling.)__'),
    ];

    $this->stage = 5;

    $this->flow = [
      'type' => NODE_XOR,
      'childs' => [
        [
          'type' => NODE_SEQ,
          'childs' => [
            [
              'action' => WISHCHILDREN,
              'args' => ['constraints' => ['freeRoom']],
            ],
            [
              'action' => FURNISH,
              'args' => [
                'types' => [
                  'D_AddDwelling',
                  'D_CoupleDwelling',
                  'D_Dwelling',
                  'D_MixedDwelling',
                  'D_SimpleDwelling1',
                  'D_SimpleDwelling2',
                ],
              ],
            ],
          ],
        ],
        ['action' => GAIN, 'args' => [GOLD => 3]],
      ],
    ];
  }

  public function isSupported($players, $options)
  {
    return false; // will be updated when family life is flipped
  }
}