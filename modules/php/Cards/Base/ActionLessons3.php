<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionLessons3 extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_Lessons3';
        $this->name = clienttranslate("Lessons");
        $this->minPlayers = 3;

        $this->card_text = [
            clienttranslate("Pay 2 [F]"),
        ];

        $this->actions = [
            0 => ['type' => LESSONS3, 'mandatory' => true],
        ];
    }
}
