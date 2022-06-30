<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionClayPit extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_ClayPit';
        $this->name = clienttranslate("Clay pit");
        $this->accumulation = ['clay' => 1];

        $this->actions = [
            0 => ['type' => CLAYPIT, 'mandatory' => true, 'auto' => true],
        ];
    }
}
