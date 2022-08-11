<?php
namespace CAV\Buildings\G;

class G_WoodSupplier extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_WoodSupplier';
    $this->category = 'material';
    $this->name = clienttranslate('Wood Supplier');
    $this->desc = [clienttranslate('at the beginning of the next 7 rounds')];
    $this->tooltip = [
      clienttranslate(
        'When building the Wood supplier, place 1 Wood from the general supply on the next 7 Round spaces.'
      ),
      clienttranslate('At the beginning of these rounds, you receive the Wood.'),
    ];
    $this->cost = [STONE => 1];
    $this->vp = 2;
  }
}
