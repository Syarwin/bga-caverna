<?php

namespace CAV\Buildings\D;

class D_StartDwelling extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'D_StartDwelling';
    $this->category = 'dwelling';
    $this->name = clienttranslate('Entry level Dwelling');
    $this->dwelling = 2;
    $this->nbInBox = 0;
    $this->animalHolder = 2;
    $this->desc = [clienttranslate('room for exactly 2 Dwarfs and 1 pair of animals')];
  }

  public function isSupported($players, $options)
  {
    return false; // Make sure StartDwelling are not created on building boards
  }

  public function onPlayerComputeDropZones($player, &$args)
  {
    $args['zones'][] = [
      'type' => 'room',
      'card_id' => $this->type,
      'capacity' => 2,
      'locations' => [$this->getPos()],
    ];
  }

  public function isConsideredDwelling()
  {
    return true;
  }
}
