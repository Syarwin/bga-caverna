<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionHouseRedevelopment extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_HouseRedevelopment';
        $this->name = clienttranslate("House redevelopment");
        $this->stage = 2;
        $this->card_text = [
            clienttranslate("Renovation"),
            clienttranslate("then"),
        ];

        $this->actions = [
            0 => ['type' => RENOVATION, 'mandatory' => true],
            1 => ['type' => IMPROVEMENT, 'mandatory' => false],
        ];
    }
}
