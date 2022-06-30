<?php
namespace AGR\Cards\B;

class B155_ArtTeacher extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B155_ArtTeacher';
    $this->name = ('Art Teacher');
    $this->deck = 'B';
    $this->number = 155;
    $this->category = GOODS_PROVIDER;
    $this->desc = [
      (
        'When you play this card, you immediately get 1 wood and 1 reed. Each time you pay an occupation cost, you can use food from the "traveling Players" accumulation space.'
      ),
    ];
    $this->players = '4+';
    $this->implemented = false;
  }
}
