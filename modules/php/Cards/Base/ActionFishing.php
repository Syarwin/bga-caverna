<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionFishing extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_Fishing';
        $this->name = clienttranslate("Fishing");
        $this->accumulation = ['food' => 1];

        $this->actions = [
            0 => ['type' => FISHING, 'mandatory' => true, 'auto' => true],
        ];
    }
}
