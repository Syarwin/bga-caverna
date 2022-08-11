<?php
namespace CAV\Buildings\G;

class G_Seam extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_Seam';
    $this->category = 'material';
    $this->name = clienttranslate('Seam');
    $this->desc = [clienttranslate('for each new')];
    $this->tooltip = [
      clienttranslate(
        'From now on, you will immediately get 1 Ore from the general supply on top of each Stone you get (regardless of how you got the Stone).'
      ),
    ];
    $this->costs = [[WOOD => 2]];
    $this->vp = 1;
  }
}
