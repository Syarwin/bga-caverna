<?php
namespace CAV\Buildings\G;

class G_SlaughteringCave extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_SlaughteringCave';
    $this->category = 'food';
    $this->name = clienttranslate('Slaughtering Cave');
    $this->desc = [clienttranslate('+1<FOOD> for each animal that you convert into food')];
    $this->tooltip = [
      clienttranslate('You get 1 more Food from the general supply for each Farm animal that you convert into Food.'),
      clienttranslate(
        '(You cannot convert Dogs into Food. If you convert 2 animals into Food at the same time – like 2 Donkeys – you will get 2 more Food. You cannot use the Slaughtering cave in combination with the Hunting parlor.)'
      ),
    ];
    $this->cost = [WOOD => 2, STONE => 2];
    $this->vp = 2;
    $this->beginner = true;

    $this->exchanges = [
      [
        'source' => $this->name,
        'flag' => $this->id,
        'triggers' => null,
        'from' => [
          \SHEEP => 1,
        ],
        'to' => [
          FOOD => 2,
        ],
      ],
      [
        'source' => $this->name,
        'flag' => $this->id,
        'triggers' => null,
        'from' => [
          \PIG => 1,
        ],
        'to' => [
          FOOD => 3,
        ],
      ],
      [
        'source' => $this->name,
        'flag' => $this->id,
        'triggers' => null,
        'from' => [
          \CATTLE => 1,
        ],
        'to' => [
          FOOD => 4,
        ],
      ],
      [
        'source' => $this->name,
        'flag' => $this->id,
        'triggers' => null,
        'from' => [
          \DONKEY => 1,
        ],
        'to' => [
          FOOD => 2,
        ],
      ],
      [
        'source' => $this->name,
        'flag' => $this->id,
        'triggers' => null,
        'from' => [
          \DONKEY => 2,
        ],
        'to' => [
          FOOD => 5,
        ],
      ],
    ];
  }
}
