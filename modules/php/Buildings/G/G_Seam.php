<?php
namespace CAV\Buildings\G;

class G_Seam extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'G_Seam';
    $this->category = 'material';
    $this->name = clienttranslate('Seam');
    $this->desc = [clienttranslate('+1<ORE> for each new <STONE>')];
    $this->tooltip = [
      clienttranslate(
        'From now on, you will immediately get 1 Ore from the general supply on top of each Stone you get (regardless of how you got the Stone).'
      ),
    ];
    $this->cost = [WOOD => 2];
    $this->vp = 1;
  }

  public function isListeningTo($event)
  {
    if ($this->isActionEvent($event, 'Gain')) {
      foreach ($event['meeples'] as $m) {
        if ($m['type'] == STONE) {
          return true;
        }
      }
    }

    if ($this->isCollectEvent($event)) {
      foreach ($event['meeples'] as $m) {
        if ($m['type'] == STONE) {
          return true;
        }
      }
    }
    return false;
  }

  public function gainOre($event)
  {
    $stone = 0;
    foreach ($event['meeples'] as $m) {
      if ($m['type'] == STONE) {
        $stone++;
      }
    }

    if ($stone > 0) {
      return $this->gainNode([ORE => $stone]);
    }
  }

  public function onPlayerAfterCollect($player, $event)
  {
    return $this->gainOre($event);
  }

  public function onPlayerAfterGain($player, $event)
  {
    return $this->gainOre($event);
  }
}
