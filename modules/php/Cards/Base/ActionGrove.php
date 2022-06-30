<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionGrove extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_Grove';
        $this->name = clienttranslate("Grove");
        $this->accumulation = ['wood' => 2];
        $this->minPlayers = 3;

        $this->actions = [
            0 => ['type' => GROVE, 'mandatory' => true, 'auto' => true],
        ];
    }
}
