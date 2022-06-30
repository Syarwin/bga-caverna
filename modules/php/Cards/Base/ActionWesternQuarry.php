<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionWesternQuarry extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_WesternQuarry';
        $this->name = clienttranslate("Western Quarry");
        $this->accumulation = ['stone' => 1];
        $this->stage = 2;

        $this->actions = [
            0 => ['type' => WESTERNQUARRY, 'mandatory' => true, 'auto' => true],
        ];
    }
}
