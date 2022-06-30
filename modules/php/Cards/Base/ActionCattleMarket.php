<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionCattleMarket extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_CattleMarket';
        $this->name = clienttranslate("Cattle market");
        $this->accumulation = ['cattle' => 1];
        $this->stage = 4;

        $this->actions = [
            0 => ['type' => CATTLEMARKET, 'mandatory' => true, 'auto' => true],
        ];
    }
}
