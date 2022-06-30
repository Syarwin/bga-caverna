<?php
namespace AGR\Cards\D;
use AGR\Core\Notifications;

class D148_DomesticianExpert extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D148_DomesticianExpert';
    $this->name = clienttranslate('Domestician Expert');
    $this->deck = 'D';
    $this->number = 148;
    $this->category = FARM_PLANNER;
    $this->desc = [
      clienttranslate('You can keep 2 sheep on the border between each pair of orthogonally adjacent rooms.'),
    ];
    $this->players = '4+';
  }

  public function onBuy($player)
  {
    Notifications::updateDropZones($player);
  }

  public function onPlayerComputeDropZones($player, &$args)
  {
    $edges = $player->board()->getSurroundedByRoomsEdges();
    foreach ($edges as $edge) {
      $args['zones'][] = [
        'type' => 'D148_special',
        'constraints' => [SHEEP],
        'capacity' => 2,
        'locations' => [
          [
            'x' => $edge['x'],
            'y' => $edge['y'],
          ],
        ],
      ];
    }
  }
}
