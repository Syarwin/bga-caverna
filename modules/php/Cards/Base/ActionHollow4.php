<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionHollow4 extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_Hollow4';
        $this->name = clienttranslate("Hollow");
        $this->accumulation = ['clay' => 2];
        $this->minPlayers = 4;

        $this->actions = [
            0 => ['type' => HOLLOW4, 'mandatory' => true, 'auto' => true],
        ];
    }
}
