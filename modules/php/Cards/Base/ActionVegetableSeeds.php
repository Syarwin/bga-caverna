<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionVegetableSeeds extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_VegetableSeeds';
        $this->name = clienttranslate("Vegetable Seeds");
        $this->stage = 3;

        $this->actions = [
            0 => ['type' => VEGETABLESEEDS, 'mandatory' => true],
        ];
    }
}
