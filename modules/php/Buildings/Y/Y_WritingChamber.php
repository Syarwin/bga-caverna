<?php
namespace CAV\Buildings\G;

class Y_WritingChamber extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_WritingChamber';
    $this->category = 'bonus';
    $this->name = clienttranslate('Writing Chamber');
    $this->tooltip = [
      clienttranslate(
        'When scoring, the Writing chamber can prevent the loss of up to 7 Gold points. You lose points for Begging markers, unused spaces on your Home board and missing Farm animals. Correction is automatically managed by BGA.'
      ),
    ];
    $this->cost = [STONE => 2];
  }
}
