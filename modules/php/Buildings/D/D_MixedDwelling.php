<?php
namespace CAV\Buildings\D;

class D_MixedDwelling extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'D_MixedDwelling';
    $this->category = 'dwelling';
    $this->name = clienttranslate('Mixed Dwelling');
    $this->desc = [clienttranslate('Room for 1 dwarf and 1 pair of animals')];
    $this->tooltip = [
      clienttranslate('The Mixed dwelling provides room for exactly 1 Dwarf and 2 animals of the same type.'),
    ];
    $this->dwelling = 1;
    $this->cost = [WOOD => 5, STONE => 4];
    $this->vp = 4;
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
}
