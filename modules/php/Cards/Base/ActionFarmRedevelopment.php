<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionFarmRedevelopement extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_FarmRedevelopement';
        $this->name = clienttranslate("Farm Redevelopement");
        $this->stage = 6;
        $this->card_text = [
            clienttranslate("Renovation"),
            clienttranslate("then"),
            clienttranslate("fencing")
        ];

        $this->actions = [
            0 => ['type' => RENOVATION, 'mandatory' => true],
            1 => ['type' => FENCING, 'mandatory' => false],
        ];
    }
}
