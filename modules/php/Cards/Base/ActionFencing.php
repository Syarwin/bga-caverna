<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionFencing extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_Fencing';
        $this->name = clienttranslate("Fencing");
        $this->stage = 1;
        $this->card_text = [
            clienttranslate("Build fences"),
        ];

        $this->actions = [
            0 => ['type' => FENCING, 'mandatory' => true],
        ];
    }
}
