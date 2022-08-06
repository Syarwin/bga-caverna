<?php
namespace CAV\ActionCards;

class ActionImitation extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionImitation';
    $this->name = clienttranslate('Imitation');
    $this->tooltip = [clienttranslate('TODO')]; // TODO: manage multiple instnce of cards, up to 3 cards
    $this->players = [3, 4, 5, 6, 7];

    $this->flow = [
      'action' => IMITATE,
      'args' => [
        'cost' => [FOOD => 2],
      ],
    ];
  }
}
