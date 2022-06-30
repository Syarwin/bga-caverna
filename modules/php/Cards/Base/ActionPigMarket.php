<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionPigMarket extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_PigMarket';
        $this->name = clienttranslate("Pig market");
        $this->accumulation = ['pig' => 1];
        $this->stage = 3;

        $this->actions = [
            0 => ['type' => PIGMARKET, 'mandatory' => true, 'auto' => true],
        ];
    }
}
