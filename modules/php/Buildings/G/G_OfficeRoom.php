<?php
namespace CAV\Buildings\G;

class G_OfficeRoom extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_OfficeRoom';
    $this->category = 'dwelling';
    $this->name = clienttranslate('Office room');
    $this->desc = [clienttranslate('twin tiles may overhang;'), clienttranslate('every time you do so:')];
    $this->tooltip = [
      clienttranslate(
        'When placing twin tiles, you only need to place half of the tile on your Home board, the other half may overhang.'
      ),
      clienttranslate('Every time you do so, take 2 Gold from the general supply.'),
    ];
    $this->cost = [STONE => 1];
    $this->vp = 0;
  }
}
