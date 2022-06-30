<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionGrainSeeds extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_GrainSeeds';
        $this->name = clienttranslate("Grain Seeds");

        $this->actions = [
            0 => ['type' => GRAINSEEDS, 'mandatory' => true, 'auto' => true],
        ];
    }
}
