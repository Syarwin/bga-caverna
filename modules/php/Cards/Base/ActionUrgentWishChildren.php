<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionUrgentWishChildren extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_UrgentWishChildren';
        $this->name = clienttranslate("Urgent wish for Children");
        $this->stage = 5;
        $this->card_text = [
            clienttranslate("Growth without room"),
        ];

        $this->actions = [
            0 => ['type' => URGENTWISHCHILDREN, 'mandatory' => true],
        ];
    }
}
