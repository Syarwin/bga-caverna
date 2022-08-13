<?php
namespace CAV\Buildings\G;

class Y_PrayerChamber extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_PrayerChamber';
    $this->category = 'bonus';
    $this->name = clienttranslate('Prayer Chamber');
    $this->tooltip = [
      clienttranslate(
        'When scoring, the Prayer chamber will be worth 8 Bonus points if none of your Dwarfs that are in play have a Weapon'
      ),
      clienttranslate(
        '__(You can use the Peaceful cave to trade the Weapons of your Dwarfs for Food. This way, you can get rid of the Weapons of all of your Dwarfs and get the 8 Bonus points for the Prayer chamber if you own that one as well.)__'
      ),
    ];
    $this->cost = [WOOD => 2];
  }
}
