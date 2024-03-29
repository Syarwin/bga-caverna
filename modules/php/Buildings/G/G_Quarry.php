<?php
namespace CAV\Buildings\G;

use CAV\Core\Globals;

class G_Quarry extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_Quarry';
    $this->category = 'material';
    $this->name = clienttranslate('Quarry');
    $this->desc = [clienttranslate('+1<STONE> for each newborn <DONKEY>')];
    $this->tooltip = [
      clienttranslate('From now on, you will immediately get 1 Stone from the general supply for each newborn Donkey.'),
      clienttranslate('(This does not apply to Donkeys that you get from game boards or for Rubies.)'),
    ];
    $this->cost = [WOOD => 1];
    $this->vp = 2;
  }

  public function isListeningTo($event)
  {
    return $this->isActionEvent($event, 'Reorganize') && ($event['trigger'] == \HARVEST || $event['trigger'] == \BREED);
  }

  public function onPlayerAfterReorganize($player, $event)
  {
    $createdAnimals = Globals::getBreed();
    $donkeys = 0;
    foreach ($createdAnimals as $animal) {
      if ($animal == DONKEY) {
        $donkeys++;
      }
    }
    if ($donkeys > 0) {
      return $this->gainNode([STONE => $donkeys]);
    }
  }
}
