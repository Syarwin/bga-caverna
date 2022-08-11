<?php
namespace CAV\Buildings\G;

class G_Trader extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_Trader';
    $this->category = 'material';
    $this->name = clienttranslate('Trader');
    $this->desc = [clienttranslate('at anytime before scoring')];
    $this->tooltip = [
      clienttranslate(
        'At any time before scoring, you can buy 1 Wood, 1 Stone and 1 Ore from the general supply for a total of 2 Gold. (You can only buy the full set.)'
      ),
      clienttranslate(
        'A player who has already built the Spare part storage and decides to build the Trader as well must place the Trader on top of the Spare part storage, thus overbuilding the Spare part storage. (Do not return the Spare part storage to the general supply.)'
      ),
      clienttranslate(
        'He cannot use the Spare part storage any longer and does not get any points at the end of the game for it. The same applies if a player has already built the Trader and decides to build the Spare part storage. (In general, Furnishing tiles cannot be overbuilt.)'
      ),
    ];
    $this->cost = [WOOD => 1];
    $this->vp = 2;
  }
}
