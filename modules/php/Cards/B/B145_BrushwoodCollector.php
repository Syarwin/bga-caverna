<?php
namespace AGR\Cards\B;

use AGR\Helpers\Utils;

class B145_BrushwoodCollector extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B145_BrushwoodCollector';
    $this->name = clienttranslate('Brushwood Collector');
    $this->deck = 'B';
    $this->number = 145;
    $this->category = BUILDING_RESOURCE_PROVIDER;
    $this->desc = [
      clienttranslate(
        'Each time you __renovate__ or __build a room__, you can replace the required 1 or 2 <REED> with a total of 1 <WOOD>.'
      ),
    ];
    $this->players = '3+';
  }

  public function onPlayerComputeCostsConstruct($player, &$args)
  {
    foreach ($args['costs']['trades'] as $trade) {
      $keys = array_diff(array_keys($trade), ['nb', 'max']);

      if (in_array(REED, $keys)) {
        $trade[WOOD] = ($trade[WOOD] ?? 0) + 1;
        unset($trade[REED]);
        $trade['sources'][] = $this->id;
        Utils::addCost($args['costs'], $trade);
      }
    }
  }

  public function onPlayerComputeCostsRenovation($player, &$args)
  {
    foreach ($args['costs']['fees'] as $fee) {
      $keys = array_diff(array_keys($fee), ['nb', 'max']);

      if (in_array(REED, $keys)) {
        $fee[WOOD] = ($fee[WOOD] ?? 0) + 1;
        $fee['sources'][] = $this->id;
        unset($fee[REED]);
        Utils::addFees($args['costs'], $fee);
      }
    }
  }
}
