<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionLessons4 extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_Lessons4';
        $this->name = clienttranslate("Lessons");
        $this->minPlayers = 4;

        $this->card_text = [
            clienttranslate("Pay 2 [F]"),
            clienttranslate("The first and second occupation played cost 1 [F]"),
        ];

        $this->actions = [
            0 => ['type' => LESSONS4, 'mandatory' => true],
        ];
    }
}
