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
    $this->cost = [];
    $this->vp = 0;
  }

  public function getCosts($player, $args = [])
  {
    return NO_COST;
  }

  public function isListeningTo($event)
  {
    if ($this->isActionEvent($event, 'Gain')) {
      foreach ($event['meeples'] as $m) {
        if ($m['type'] == DOG) {
          return true;
        }
      }
    }

    if ($this->isCollectEvent($event)) {
      foreach ($event['meeples'] as $m) {
        if ($m['type'] == DOG) {
          return true;
        }
      }
    }
    return false;
  }

  public function gainWood($event)
  {
    $dogs = 0;
    foreach ($event['meeples'] as $m) {
      if ($m['type'] == DOG) {
        $dogs++;
      }
    }

    if ($dogs > 0) {
      return $this->gainNode([WOOD => $dogs]);
    }
  }

  public function onPlayerAfterCollect($player, $event)
  {
    return $this->gainWood($event);
  }

  public function onPlayerAfterGain($player, $event)
  {
    return $this->gainWood($event);
  }
}
