<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionTravelingPlayers extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_TravelingPlayers';
        $this->name = clienttranslate("Traveling Players");
        $this->accumulation = ['food' => 1];
        $this->minPlayers = 4;

        $this->actions = [
            0 => ['type' => TRAVELINGPLAYERS, 'mandatory' => true, 'auto' => true],
        ];
    }
}
