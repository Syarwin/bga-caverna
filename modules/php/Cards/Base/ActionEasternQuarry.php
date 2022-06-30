<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionEasternQuarry extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_EasternQuarry';
        $this->name = clienttranslate("Eastern Quarry");
        $this->accumulation = ['stone' => 1];
        $this->stage = 4;

        $this->actions = [
            0 => ['type' => EASTERNQUARRY, 'mandatory' => true, 'auto' => true],
        ];
    }
}
