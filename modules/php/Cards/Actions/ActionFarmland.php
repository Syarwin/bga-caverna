<?php
namespace AGR\Cards\Actions;

class ActionFarmland extends \AGR\Models\ActionCard
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'ActionFarmland';
    $this->name = clienttranslate('Farmland');
    $this->desc = ['<FIELD>'];
    $this->tooltipDesc = [clienttranslate('[Plow a field]'), '<FIELD>'];
    $this->tooltip = [clienttranslate('You can plow one field')];

    $this->flow = [
      'action' => PLOW,
    ];
  }
}
