<?php
namespace AGR\Cards\B;

class B9_BeatingRod extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B9_BeatingRod';
    $this->name = clienttranslate('Beating Rod');
    $this->deck = 'B';
    $this->number = 9;
    $this->category = GOODS_PROVIDER;
    $this->desc = [clienttranslate('You can immediately choose to either get 1 <REED> or exchange 1 <REED> for 1 <CATTLE>.')];
    $this->passing = true;
    $this->newSet = true;
  }
  
  public function onBuy($player)
  {
    return [
      'type' => NODE_XOR,
      'childs' => [
	    $this->gainNode([REED => 1]), 
		$this->payGainNode([REED => 1],[CATTLE => 1]),
      ]
    ];
  }  
}
