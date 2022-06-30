<?php
namespace AGR\Cards\D;
use AGR\Helpers\Utils;

class D88_Millwright extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D88_Millwright';
    $this->name = clienttranslate('Millwright');
    $this->deck = 'D';
    $this->number = 88;
    $this->category = FARM_PLANNER;
    $this->desc = [
      clienttranslate(
        'You immediately get 1 <GRAIN>. Each time you build fences, stables, and rooms, or renovate your house, you can replace up to 2 building resources of any type with 1 <GRAIN> each.'
      ),
    ];
    $this->players = '1+';
  }

  public function onBuy($player)
  {
    return $this->gainNode([GRAIN => 1]);
  }

  public function addMillwrightBonus(&$args)
  {
    $choices = [
      [WOOD => -1, GRAIN => 1],
      [CLAY => -1, GRAIN => 1],
      [STONE => -1, GRAIN => 1],
      [REED => -1, GRAIN => 1],
    ];
    Utils::addBonusChoices($args['costs'], $choices, $this->id, true);
    Utils::addBonusChoices($args['costs'], $choices, $this->id, true);
  }

  public function onPlayerComputeCostsFencing($player, &$args)
  {
    $this->addMillwrightBonus($args);
  }

  public function onPlayerComputeCostsConstruct($player, &$args)
  {
    $this->addMillwrightBonus($args);
  }

  public function onPlayerComputeCostsRenovation($player, &$args)
  {
    $this->addMillwrightBonus($args);
  }

  public function onPlayerComputeCostsStables($player, &$args)
  {
    $this->addMillwrightBonus($args);
  }
}
