<?php
namespace CAV\Buildings\G;

class G_RubySupplier extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_RubySupplier';
    $this->category = 'material';
    $this->name = clienttranslate('Ruby Supplier');
    $this->desc = [clienttranslate('at the beginning of the next 4 rounds')];
    $this->tooltip = [
      clienttranslate(
        'When building the Ruby supplier, place 1 Ruby from the general supply on the next 4 Round spaces.'
      ),
      clienttranslate('At the beginning of these rounds, you receive the Wood.'),
    ];
    $this->costs = [[WOOD => 2, STONE => 2]];
    $this->vp = 2;
  }
}
