<?php
namespace AGR\Cards\Actions;

class ActionAnimalMarketAdd extends \AGR\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionAnimalMarketAdd';
    $this->name = clienttranslate('Animal market');

    // $this->actions = [
    //   [
    //     'type' => ANIMALMARKET,
    //     'mandatory' => true,
    //   ],
    // ];

    $this->flow = [
      'type' => NODE_XOR,
      'childs' => [
        ['action' => GAIN, 'actionId' => 'Market1', 'args' => [SHEEP => 1, FOOD => 1]],
        ['action' => GAIN, 'actionId' => 'Market2', 'args' => [PIG => 1]],
        ['action' => GAIN, 'actionId' => 'Market3', 'args' => [CATTLE => 1, FOOD => -1]],
      ],
    ];
  }
}
