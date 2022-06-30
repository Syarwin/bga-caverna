<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionGrainUtilization extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_GrainUtilization';
        $this->name = clienttranslate("Grain utilization");
        $this->stage = 1;

        $this->actions = [
            0 => ['type' => SOW],
            1 => ['type' => BAKE]
        ];
    }
}
