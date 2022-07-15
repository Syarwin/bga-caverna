<?php
namespace CAV\Cards\ActionCards;

class ActionDriftMining extends \CAV\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionDriftMining';
    $this->name = clienttranslate('Drift Mining');
    $this->desc = ['+1<SHEEP> +1<FOOD>', '+1<PIG>', '+1<CATTLE> -1<FOOD>'];
    $this->tooltip = [
      clienttranslate('Take all the Stone that has accumulated on this Action space.'),
      clienttranslate(
        'Additionally, you may place a Cavern/Tunnel twin tile on 2 adjacent empty Mountain spaces of your Home board.'
      ),
      clienttranslate(
        'If you place the twin tile on one of the underground water sources, you will immediately get 1 or 2 Food from the general supply.'
      ),
      clienttranslate(
        'You have to place the twin tile adjacent to an already occupied Mountain space, i.e. you have to extend your cave system.'
      ),
    ];
    $this->players = [1, 2, 3];

    $this->flow = [];
  }
}
