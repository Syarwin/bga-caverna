<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionSheepMarket extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_SheepMarket';
        $this->name = clienttranslate("Sheep market");
        $this->accumulation = ['sheep' => 1];
        $this->stage = 1;

        $this->actions = [
            0 => ['type' => SHEEPMARKET, 'mandatory' => true, 'auto' => true],
        ];
    }
}
