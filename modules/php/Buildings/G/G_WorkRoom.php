<?php
namespace CAV\Buildings\G;

class G_WorkRoom extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_WorkRoom';
    $this->category = 'dwelling';
    $this->name = clienttranslate('Work Room');
    $this->desc = [clienttranslate('you may furnish'), clienttranslate('and')];
    $this->tooltip = [
      clienttranslate(
        'Instead of Mines, you can build Furnishing tiles on your Tunnels and Deep tunnels when taking a Furnish a cavern or Furnish a dwelling action.'
      ),
      clienttranslate(
        '(Remember you can spend 1 Ruby to get a Tunnel tile. You cannot build the Work room on a Tunnel tile.)'
      ),
    ];
    $this->costs = [[STONE => 1]];
    $this->vp = 2;
  }
}
