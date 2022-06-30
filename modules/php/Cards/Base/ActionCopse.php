<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionCopse extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_Copse';
        $this->name = clienttranslate("Copse");
        $this->accumulation = ['wood' => 1];
        $this->minPlayers = 4;

        $this->actions = [
            0 => ['type' => COPSE, 'mandatory' => true, 'auto' => true],
        ];
    }
}
