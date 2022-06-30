<?php
namespace AGR\Cards\D;
use AGR\Core\Globals;
use AGR\Core\Engine;
use AGR\Helpers\Utils;

class D66_PotterCeramics extends \AGR\Models\MinorImprovement
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->id = 'D66_PotterCeramics';
    $this->name = clienttranslate('Potter Ceramics');
    $this->deck = 'D';
    $this->number = 66;
    $this->category = CROP_PROVIDER;
    $this->desc = [
      clienttranslate('Each time before you take a __Bake Bread__ action, you can exchange 1 <CLAY> for 1 <GRAIN>.'),
    ];
  }

  public function onPlayerIsDoable($player, &$args)
  {
    if ($args['isDoable'] && $args['action'] != EXCHANGE) {
      return;
    }

    $ctxArgs = $args['ctx'] == null ? null : (is_array($args['ctx']) ? $args['ctx'] : $args['ctx']->getArgs());
    if (($ctxArgs['trigger'] ?? ANYTIME) == BREAD && $player->canBake() && $player->canPayCost([CLAY => 1])) {
      $args['isDoable'] = true;
    }
  }

  public function isListeningTo($event)
  {
    return $this->isBeforeEvent($event, 'Exchange') && ($event['args']['trigger'] ?? ANYTIME) == BREAD;
  }

  public function onPlayerBeforeExchange($player, $event)
  {
    return [
      'type' => NODE_SEQ,
      'pId' => $this->pId,
      'optional' => true,
      'childs' => [
        [
          'action' => PAY,
          'pId' => $this->pId,
          'args' => [
            'nb' => 1,
            'costs' => Utils::formatCost([CLAY => 1]),
            'source' => clienttranslate('Potter Ceramics effect'),
            'cardId' => $this->id,
          ],
        ],
        [
          'action' => GAIN,
          'pId' => $this->pId,
          'args' => [GRAIN => 1, 'cardId' => $this->id],
          'source' => $this->name,
        ],
        [
          'action' => SPECIAL_EFFECT,
          'args' => [
            'cardId' => $this->id,
            'method' => 'makeBakeMandatory',
          ],
        ],
      ],
    ];
  }

  public function makeBakeMandatory()
  {
    $parNode = Engine::$tree->getNextUnresolved()->getParent()->getParent();
    $parNode->getNextSibling()->enforceMandatory();
    Engine::save();
  }
}
