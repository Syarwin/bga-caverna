<?php
namespace AGR\Cards\B;
use AGR\Core\Globals;

class B96_TreeFarmJoiner extends \AGR\Models\Occupation
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'B96_TreeFarmJoiner';
    $this->name = clienttranslate('Tree Farm Joiner');
    $this->deck = 'B';
    $this->number = 96;
    $this->category = ACTIONS_BOOSTER;
    $this->desc = [
      clienttranslate(
        'Place 1 <WOOD> on each of the next 2 odd-numbered round spaces. At the start of these rounds, you get the <WOOD> and, immediately afterward, a __Minor Improvement__ action.'
      ),
    ];
    $this->players = '1+';
    $this->newSet = true;
  }
  
  public function onBuy($player)
  {  
    $turns = (Globals::getTurn() % 2 == 0) ? ['+1', '+3'] : ['+2', '+4'];    
    
    return $this->futureMeeplesNode([WOOD => 1], $turns, true, $this->id);
  }

  public function getReceiveFlow($meeple)
  {
    return [
      'type' => NODE_SEQ,
      'childs' => [
        $this->receiveNode($meeple['id'], true),
        [
          'action' => IMPROVEMENT,
          'optional' => true,
          'args' => [
            'types' => [MINOR],
          ],
        ]
      ]
    ];
  }
}
