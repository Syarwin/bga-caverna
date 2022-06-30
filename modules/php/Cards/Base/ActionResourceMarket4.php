<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionResourceMarket4 extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_ResourceMarket4';
        $this->name = clienttranslate("Resource market");
        $this->minPlayers = 4;

        $this->actions = [
            0 => ['type' => RESOURCEMARKET4, 'mandatory' => true, 'auto' => true],
        ];
    }
}
