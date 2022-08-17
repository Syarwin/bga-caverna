<?php
namespace CAV\Buildings\G;

class G_CuddleRoom extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_CuddleRoom';
    $this->category = 'dwelling';
    $this->name = clienttranslate('Cuddle Room');
    $this->desc = [clienttranslate('room for as many'), clienttranslate('as you have')];
    $this->tooltip = [
      clienttranslate(
        'The Cuddle room can hold a number of Sheep equal to the number of Dwarfs you have. It cannot hold any other Farm animals.'
      ),
      clienttranslate('(Both Dwarf discs on a “Family growth“ action count towards this number.)'),
    ];
    $this->cost = [WOOD => 1];
    $this->vp = 2;
    $this->animalHolder = true;
  }

  public function onPlayerComputeDropZones($player, &$args)
  {
    $capacity = $player->countDwarfs();

    if ($capacity > 0) {
      $args['zones'][] = [
        'type' => 'card',
        'card_id' => $this->type,
        'constraints' => [SHEEP],
        'capacity' => $capacity,
        'locations' => [['type' => 'card', 'card_id' => $this->type]],
      ];
    }
  }
}
