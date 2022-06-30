<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionFarmExpansion extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_FarmExpansion';
        $this->name = clienttranslate("Farm expansion");

        $this->card_text = [
            clienttranslate("Build rooms"),
            clienttranslate("and/or"),
            clienttranslate("Build stables")
        ];

        $this->actions = [
            0 => ['type' => CONSTRUCT, 'mandatory' => false],
            1 => ['type' => STABLES, 'mandatory' => false]
        ];
    }
}
