<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionHollow extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_Hollow';
        $this->name = clienttranslate("Hollow");
        $this->accumulation = ['clay' => 1];
        $this->minPlayers = 3;

        $this->actions = [
            0 => ['type' => HOLLOW, 'mandatory' => true, 'auto' => true],
        ];
    }
}
