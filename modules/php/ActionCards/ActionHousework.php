<?php
namespace CAV\ActionCards;

class ActionHousework extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionHousework';
    $this->name = clienttranslate('Housework');
    $this->tooltip = [
      clienttranslate('Take 1 Dog from the general supply.'),
      clienttranslate('Additionally or alternatively, take a Furnishing tile, pay its building costs and place it on an
empty Cavern in your cave system.'),
      clienttranslate('You may choose from all Furnishing tiles (including Dwellings). If you cannot place a Furnishing tile on your
Home board, you may not take any.'),
    ];
    $this->desc = [clienttranslate('and / or')];
    $this->players = [1, 2, 3, 4, 5, 6, 7];

    $this->flow = [
      'type' => NODE_OR,
      'childs' => [['action' => GAIN, 'args' => [DOG => 1]], ['action' => FURNISH]],
    ];
  }
}
