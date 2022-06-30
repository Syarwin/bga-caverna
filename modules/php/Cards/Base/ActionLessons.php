<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionLessons extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_Lessons';
        $this->name = clienttranslate("Lessons");

        $this->card_text = [
            clienttranslate("Pay 1 [F]"),
            clienttranslate("The first occupation played is free"),
        ];

        $this->actions = [
            0 => ['type' => LESSONS, 'mandatory' => true],
        ];
    }
}
