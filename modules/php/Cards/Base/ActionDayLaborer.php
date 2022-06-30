<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionDayLaborer extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_DayLaborer';
        $this->name = clienttranslate("Day laborer");

        $this->actions = [
            0 => ['type' => DAYLABOURER, 'mandatory' => true, 'auto' => true],
        ];
    }
}
