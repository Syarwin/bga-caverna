<?php
namespace CAV\Buildings\G;

class G_BreedingCave extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_BreedingCave';
    $this->category = 'food';
    $this->name = clienttranslate('Breeding Cave');
    $this->desc = [clienttranslate('for each animal bred')];
    $this->tooltip = [
      clienttranslate(
        'Every time you breed your animals __(in the Breeding phase or via the corresponding Expedition loot item)__, you will get 1 Food for each newborn Farm animal.'
      ),
      clienttranslate(
        'If you get a newborn for each of the four types of Farm animals, you will get one more Food for a total of 5 Food. Take the Food from the general supply.'
      ),
    ];
    $this->cost = [STONE => 1, GRAIN => 1];
    $this->vp = 2;
  }

  public function isListeningTo($event)
  {
    return $this->isActionEvent($event, 'Reorganize') && ($event['trigger'] == \HARVEST || $event['trigger'] == \BREED);
  }

  public function onPlayerAfterReorganize($player, $event)
  {
    $createdAnimals = Globals::getBreed();
    $count = count($createdAnimals);
    if ($count == 4) {
      $count = 5;
    }
    if ($count > 0) {
      return $this->gainNode([FOOD => $count]);
    }
  }
}
