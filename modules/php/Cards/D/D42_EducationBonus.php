<?php
namespace AGR\Cards\D;

class D42_EducationBonus extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D42_EducationBonus';
    $this->name = clienttranslate('Education Bonus');
    $this->deck = 'D';
    $this->number = 42;
    $this->category = GOODS_PROVIDER;
    $this->desc = [
      clienttranslate(
        'After you play your 1st/2nd/3rd/4th/5th/6th occupation this game, you immediately get 1 <GRAIN>/<CLAY>/<REED>/<STONE>/<VEGETABLE>/<FIELD> (not retroactively).'
      ),
    ];
    $this->cost = [
      FOOD => 1,
    ];
    $this->prerequisite = clienttranslate('2 Imps');
    $this->improvementPrerequisites = ['min' => 2];
  }

  public function isListeningTo($event)
  {
    return $this->isActionEvent($event, 'Occupation');
  }

  public function onPlayerAfterOccupation($player, $event)
  {
    $n = $player->countOccupations();
    if ($n > 6) {
      return null;
    } elseif ($n == 6) {
      return $player->board()->canPlow()
        ? [
          'action' => PLOW,
          'optional' => true,
        ]
        : null;
    } else {
      $gains = [null, GRAIN, CLAY, REED, STONE, VEGETABLE];
      $gain = $gains[$n];
      return [
        'action' => GAIN,
        'pId' => $player->getId(),
        'args' => [$gain => 1],
        'source' => $this->name,
      ];
    }
  }
}
