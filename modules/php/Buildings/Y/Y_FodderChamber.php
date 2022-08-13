<?php
namespace CAV\Buildings\G;

class Y_FodderChamber extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_FodderChamber';
    $this->category = 'bonus';
    $this->name = clienttranslate('Fodder Chamber');
    $this->tooltip = [
      clienttranslate(
        'When scoring, the Fodder chamber will be worth 1 Bonus point for every 3 Farm animals you have (regardless of type).'
      ),
      clienttranslate(
        '__(For instance, you will get 1/2/3/… Bonus points for 3-5/6-8/9-11/… Farm animals, respectively. Dogs are not considered Farm animals. You will get the usual points for “Farm animals and Dogs” regardless.)__'
      ),
    ];
    $this->cost = [STONE => 1, GRAIN => 1];
  }
}
