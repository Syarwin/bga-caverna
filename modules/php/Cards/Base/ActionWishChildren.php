<?php

namespace AGR\Cards\Base;
use caverna;
// use AGR\Game\Globals;


class ActionWishChildren extends \AGR\Models\Action
{
    public function __construct($row) {
        parent::__construct($row);
        $this->id = 'Action_WishChildren';
        $this->name = clienttranslate("Wish for Children");
        $this->stage = 2;
        $this->card_text = [
            clienttranslate("Growth with room only"),
            clienttranslate("then"),
        ];

        $this->actions = [
            0 => ['type' => WISHCHILDREN, 'mandatory' => true],
            1 => ['type' => MINORIMPROVEMENT, 'mandatory' => false],
        ];
    }
}
