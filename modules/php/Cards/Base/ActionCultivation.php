<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionCultivation extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_Cultivation';
        $this->name = clienttranslate("Cultivation");
        $this->stage = 5;

        $this->card_text = [
            clienttranslate("Plow a field"),
            clienttranslate("and/or"),
            clienttranslate("Sow")
        ];

        $this->actions = [
            0 => ['type' => PLOW, 'mandatory' => false],
            1 => ['type' => SOW, 'mandatory' => false],
        ];
    }
}
