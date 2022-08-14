<?php
namespace CAV\ActionCards;

class ActionHardwareRental6 extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionHardwareRental6';
    $this->actionCardType = 'HardwareRental';
    $this->name = clienttranslate('Hardware Rental');
    $this->desc = [clienttranslate('and then / or'), clienttranslate('and then / or')];
    $this->tooltip = [
      clienttranslate('Take 2 wood from the General Supply'),
      clienttranslate('You may undertake a Level 2 expedition if your Dwarf has a Weapon'),
      clienttranslate(
        'Afterwards, you may carry out a Sow action to sow up to 2 new Grain and/or up to 2 new Vegetable fields'
      ),
    ];

    $this->players = [6, 7];
  }

  protected function getFlow($player, $dwarf)
  {
    return [
      'type' => NODE_SEQ,
      'childs' => [
        ['action' => GAIN, 'args' => [WOOD => 2]],
        [
          'type' => NODE_THEN_OR,
          'childs' => [
            [
              'action' => EXPEDITION,
              'args' => [
                'lvl' => 2,
              ],
            ],
            ['action' => SOW],
          ],
        ],
      ],
    ];
  }
}
