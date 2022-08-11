<?php
namespace CAV\Buildings\G;

class G_BreakfastRoom extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_BreakfastRoom';
    $this->category = 'dwelling';
    $this->name = clienttranslate('Breakfast Room');
    $this->desc = [clienttranslate('room for up to')];
    $this->tooltip = [clienttranslate('The Breakfast room can hold up to 3 Cattle but no other Farm animals.')];
    $this->cost = [WOOD => 1];
    $this->vp = 0;
  }
}
