<?php
namespace CAV\ActionCards;

class ActionWishChildren extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionWishChildren';
    $this->name = clienttranslate('Wish children');
    $this->desc = [clienttranslate('Either'), clienttranslate('or')];
    $this->tooltip = [
      clienttranslate('If your cave system provides more room for Dwarfs than you have Dwarfs in play, you may carry out a Family growth action. Place a Dwarf
disc from your personal supply on the Dwarf taking the action.'),
      clienttranslate(
        ' You cannot have more than five Dwarfs in play __(unless you build the “Additional dwelling”)__.'
      ),
      clienttranslate(' Alternatively, you may build a Dwelling on an empty Cavern by paying its building costs.'),
    ];

    $this->stage = 2;

    $this->flow = [
      'type' => NODE_XOR,
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
    ];
  }
}
