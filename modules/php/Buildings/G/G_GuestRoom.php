<?php
namespace CAV\Buildings\G;

class G_GuestRoom extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_GuestRoom';
    $this->category = 'dwelling';
    $this->name = clienttranslate('Guest room');
    $this->desc = [clienttranslate('"either...or" becomes "and/or" for you')];
    $this->tooltip = [
      clienttranslate('From now on when taking actions, you can read “either ... or” as “and/or”.'),
      clienttranslate(
        'There are only a few Action spaces with “either ... or" options: Urgent Wish for Children, Ruby mine construction, Growth.'
      ),
      clienttranslate(
        'In a 7-player game, you may use the Guest room on the Extension Action space to get both twin tiles (and goods) but only once per game'
      ),
    ];
    $this->costs = [[WOOD => 1, STONE => 1]];
    $this->vp = 0;
  }
}
