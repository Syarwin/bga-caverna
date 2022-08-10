<?php
namespace CAV\Buildings\D;

class D_StartDwelling extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'D_StartDwelling';
    $this->name = clienttranslate('Entry level Dwelling');
    $this->dwelling = 2;
    $this->nbInBox = 0;
    $this->animalHolder = 2;
    $this->desc = [clienttranslate('room for exactly 2 Dwarves and 1 pair of anima')];
  }

  public function isSupported($players, $options)
  {
    return false; // Make sure StartDwelling are not created on building boards
  }
}
