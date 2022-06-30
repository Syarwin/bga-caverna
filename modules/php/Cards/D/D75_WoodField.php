<?php
namespace AGR\Cards\D;

class D75_WoodField extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D75_WoodField';
    $this->name = clienttranslate('Wood Field');
    $this->deck = 'D';
    $this->number = 75;
    $this->category = BUILDING_RESOURCE_PROVIDER;
    $this->desc = [
      clienttranslate(
        'You can plant wood on this card as though it were 2 fields, but it is considered 1 field. Sow and harvest wood on this card as you would grain.'
      ),
    ];
    $this->vp = 1;
    $this->cost = [
      FOOD => 1,
    ];
    $this->prerequisite = clienttranslate('1 Occ');
    $this->occupationPrerequisites = ['min' => 1];
    $this->holder = true;
    $this->field = true;
  }

  public function getFieldDetails()
  {
    return [
      'constraints' => WOOD,
    ];
  }
}
