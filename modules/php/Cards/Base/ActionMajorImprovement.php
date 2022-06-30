<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionMajorImprovement extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_MajorImprovement';
        $this->name = clienttranslate("Improvements");
        $this->stage = 1;
        $this->card_text = [
            clienttranslate("Build 1 major improvement or play 1 minor improvement")
        ];

        $this->actions = [
            0 => ['type' => IMPROVEMENT, 'mandatory' => true],
        ];
    }
}
