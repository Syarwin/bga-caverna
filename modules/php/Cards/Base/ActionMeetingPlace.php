<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionMeetingPlace extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_MeetingPlace';
        $this->name = clienttranslate("Meeting place");

        $this->card_text = [
            clienttranslate("and/or"),
        ];

        $this->actions = [
            0 => ['type' => FIRSTPLAYER, 'mandatory' => true, 'auto' => true],
            1 => ['type' => MINOR_IMPROVEMENT, 'mandatory' => false]
        ];
    }
}
