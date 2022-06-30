<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionForest extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_Forest';
        $this->name = clienttranslate("Forest");
        $this->accumulation = ['wood' => 3];

        $this->actions = [
            0 => ['type' => FOREST, 'mandatory' => true, 'auto' => true],
        ];
    }
}
