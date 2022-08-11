<?php
namespace CAV\Buildings\G;

class G_DogSchool extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_DogSchool';
    $this->category = 'material';
    $this->name = clienttranslate('Dog School');
    $this->desc = [clienttranslate('for each new')];
    $this->tooltip = [
      clienttranslate(
        'From now on, you will immediately get 1 Wood from the general supply for each new Dog you place on your Home board.'
      ),
    ];
    $this->costs = [NO_COST];
    $this->vp = 0;
  }
}
