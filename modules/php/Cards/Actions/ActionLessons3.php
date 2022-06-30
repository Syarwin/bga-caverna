<?php
namespace AGR\Cards\Actions;

use AGR\Helpers\Utils;

class ActionLessons3 extends \AGR\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionLessons3';
    $this->name = clienttranslate('Lessons');
    $this->actionCardType = 'Lessons';
    $this->desc = [clienttranslate('[Pay] 2<FOOD>'), '1<OCCUPATION>'];
    $this->tooltipDesc = [clienttranslate('[Pay 2]') . ' <FOOD>', '1<OCCUPATION>'];
    $this->tooltip = [clienttranslate('Play exactly one occupation card from your hand')];
    $this->container = 'left';

    $this->isNotBeginner = true;
    $this->players = [3];
    $this->flow = [
      'action' => OCCUPATION,
      'args' => [
        'cost' => [FOOD => 2],
      ],
    ];
  }
}
