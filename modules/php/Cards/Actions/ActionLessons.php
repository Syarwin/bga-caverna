<?php
namespace AGR\Cards\Actions;

use AGR\Helpers\Utils;

class ActionLessons extends \AGR\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionLessons';
    $this->name = clienttranslate('Lessons');
    $this->desc = [clienttranslate('[Pay] 1<FOOD>*'), '1<OCCUPATION>'];
    $this->tooltipDesc = [clienttranslate('[Pay 1]') . ' <FOOD>*', '1<OCCUPATION>'];
    $this->tooltip = [
      clienttranslate('Play exactly one occupation card from your hand'),
      clienttranslate('* The first occupation you play in the game is free'),
    ];

    $this->isNotBeginner = true;
  }

  public function getFlow($player)
  {
    return [
      'action' => OCCUPATION,
      'args' => [
        'cost' => $player->countOccupations() == 0 ? [] : [FOOD => 1],
      ],
    ];
  }
}
