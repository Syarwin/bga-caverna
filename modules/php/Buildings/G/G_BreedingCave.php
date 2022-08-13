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
}
