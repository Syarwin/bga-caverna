<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionResourceMarket extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_ResourceMarket';
        $this->name = clienttranslate("Resource market");
        $this->minPlayers = 3;

        $this->actions = [
            0 => ['type' => RESOURCEMARKET, 'mandatory' => true],
        ];
    }
}
