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
    $this->desc = [clienttranslate('+1<RUBY> at the beginning of the next 4 rounds')];
    $this->tooltip = [
      clienttranslate(
        'When building the Ruby supplier, place 1 Ruby from the general supply on the next 4 Round spaces.'
      ),
      clienttranslate('At the beginning of these rounds, you receive the Ruby.'),
    ];
    $this->cost = [WOOD => 2, STONE => 2];
    $this->vp = 2;
    $this->beginner = true;
  }

  protected function onBuy($player, $eventData)
  {
    return $this->futureMeeplesNode([RUBY => 1], 4);
  }
}
