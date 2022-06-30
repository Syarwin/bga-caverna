<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionFarmland extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_Farmland';
        $this->name = clienttranslate("Farmland");

        $this->card_text = [
            clienttranslate("Plow a field"),
        ];

        $this->actions = [
            0 => ['type' => FARMLAND, 'mandatory' => true, 'check' => true],
        ];
    }
}
