<?php
namespace CAV\Buildings\G;

class G_StoneSupplier extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_StoneSupplier';
    $this->category = 'material';
    $this->name = clienttranslate('Stone Supplier');
    $this->desc = [clienttranslate('at the beginning of the next 5 rounds')];
    $this->tooltip = [
      clienttranslate(
        'When building the Stone supplier, place 1 Stone from the general supply on the next 5 Round spaces.'
      ),
      clienttranslate('At the beginning of these rounds, you receive the Wood.'),
    ];
    $this->costs = [[WOOD => 1]];
    $this->vp = 1;
  }
}
