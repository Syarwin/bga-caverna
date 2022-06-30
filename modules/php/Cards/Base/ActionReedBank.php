<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionReedBank extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_ReedBank';
        $this->name = clienttranslate("Reed bank");
        $this->accumulation = ['reed' => 1];

        $this->actions = [
            0 => ['type' => REEDBANK, 'mandatory' => true, 'auto' => true],
        ];
    }
}
