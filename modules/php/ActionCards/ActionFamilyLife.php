<?php
namespace CAV\ActionCards;

class ActionFamilyLife extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionFamilyLife';
    $this->name = clienttranslate('Family Life');
    $this->desc = [clienttranslate('and / or')];
    $this->tooltip = [
      clienttranslate(
        'If your cave system provides more room for Dwarfs than you have Dwarfs in play, you may carry out a Family growth action.'
      ),
      clienttranslate('Place a Dwarf disc from your personal supply on the Dwarf taking the action.'),
      clienttranslate(
        'You cannot have more than five Dwarfs in play (unless you build the “Additional dwelling”, page A3).'
      ),
      clienttranslate(
        'Additionally or alternatively, you may carry out a Sow action to sow up to 2 new Grain and/or up to 2 new Vegetable fields (as usual).'
      ),
    ];

    $this->stage = 3;
  }

  protected function getFlow($player, $dwarf)
  {
    return [
      'type' => NODE_OR,
      'childs' => [
        [
          'action' => WISHCHILDREN,
          'args' => ['constraints' => ['freeRoom']],
        ],
        [
          'action' => SOW,
        ],
      ],
    ];
  }
}
