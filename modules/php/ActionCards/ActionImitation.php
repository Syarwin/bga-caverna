<?php
namespace CAV\Cards\ActionCards;

class ActionImitation extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionImitation';
    $this->name = clienttranslate('Imitation');
    $this->tooltip = [clienttranslate('TODO')];
    $this->players = [4, 5, 6, 7];

    $this->flow = [
      'action' => IMITATE,
      'args' => [
        'cost' => [FOOD => 2],
      ],
    ];
  }
}
