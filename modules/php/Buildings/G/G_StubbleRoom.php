<?php
namespace CAV\Buildings\G;

class G_StubbleRoom extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_StubbleRoom';
    $this->category = 'dwelling';
    $this->name = clienttranslate('Stubble room');
    $this->desc = [clienttranslate('you may keep 1 farm animal on each empty field')];
    $this->tooltip = [
      clienttranslate(
        'You can keep (exactly) 1 Farm animal on each of your empty Fields (i.e. that currently do not have any Grain or Vegetables sown on them).'
      ),
    ];
    $this->cost = [WOOD => 1, ORE => 1];
    $this->vp = 1;
  }
}
