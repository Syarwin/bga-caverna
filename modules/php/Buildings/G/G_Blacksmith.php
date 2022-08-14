<?php
namespace CAV\Buildings\G;

class G_Blacksmith extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_Blacksmith';
    $this->category = 'material';
    $this->name = clienttranslate('Blacksmith');
    $this->desc = [clienttranslate('immediately'), clienttranslate('every time you forge a new weapon, you receive a discount of')];
    $this->tooltip = [
      clienttranslate('When building the Blacksmith, immediately (and only once) get 2 Ore from the general supply.'),
      clienttranslate(
        'Every time you forge a Weapon, you may pay 2 fewer Ore. Even if you do not have any Ore in your supply, you can forge a Weapon of strength 2. You can still only forge a Weapon with a maximum strength of 8 (see page 20 of the rule book).'
      ),
      clienttranslate('(You cannot apply the ability of the Blacksmith to the “Ore trading” action.)'),
    ];
    $this->cost = [WOOD => 1, STONE => 2];
    $this->vp = 3;
  }

  protected function onBuy($player, $eventData)
  {
    return $this->gainNode([ORE => 2]);
  }
}
