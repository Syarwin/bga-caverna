<?php
namespace AGR\Cards\B;

class B29_CookeryLesson extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B29_CookeryLesson';
    $this->name = ('Cookery Lesson');
    $this->deck = 'B';
    $this->number = 29;
    $this->category = POINTS_PROVIDER;
    $this->desc = [
      (
        'Each time you use a "Lesson" action space and a cooking improvement on the same turn, you get 1 bonus point.'
      ),
    ];
    $this->cost = [
      FOOD => '2',
    ];
    $this->implemented = false;
  }
}
