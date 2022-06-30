<?php
namespace AGR\Cards\D;
use AGR\Helpers\Utils;

class D121_ClayPlasterer extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D121_ClayPlasterer';
    $this->name = clienttranslate('Clay Plasterer');
    $this->deck = 'D';
    $this->number = 121;
    $this->category = BUILDING_RESOURCE_PROVIDER;
    $this->desc = [
      clienttranslate(
        'Renovating to clay only costs you exactly 1 <CLAY> and 1 <REED>. Each clay room only costs you 3 <CLAY> and 2 <REED> to build.'
      ),
    ];
    $this->players = '1+';
  }

  public function orderComputeCostsRenovation()
  {
    return [['<', 'B145_BrushwoodCollector'], ['<', 'C122_Bricklayer'], ['<', 'C14_StrawThatchedRoof']];
  }

  public function onPlayerComputeCostsRenovation($player, &$args)
  {
    if ($args['newRoomType'] == 'roomClay') {
      foreach ($args['costs']['trades'] as &$trade) {
        if (isset($trade[CLAY])) {
          unset($trade[CLAY]);
          $trade['sources'][] = $this->id;
        }
      }

      foreach ($args['costs']['fees'] as &$fee) {
        $fee[CLAY] = 1;
      }
    }
  }

  public function orderComputeCostsConstruct()
  {
    return [
      ['<', 'B145_BrushwoodCollector'],
      ['<', 'C122_Bricklayer'],
      ['<', 'C14_StrawThatchedRoof'],
      ['<', 'A123_FrameBuilder'],
    ];
  }

  public function onPlayerComputeCostsConstruct($player, &$args)
  {
    if ($args['type'] == 'roomClay') {
      Utils::addCost($args['costs'], [CLAY => 3, REED => 2], $this->id);
    }
  }
}
