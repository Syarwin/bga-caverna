<?php
namespace AGR\Actions;
use AGR\Managers\Players;
use AGR\Core\Notifications;
use AGR\Core\Engine;
use AGR\Helpers\Utils;

class Plow extends \AGR\Models\Action
{
  public function getState()
  {
    return ST_PLOW;
  }

  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = clienttranslate('Plow a field');
  }

  public function isDoable($player, $ignoreResources = false)
  {
    return $player->board()->canPlow() && ($ignoreResources || $player->canBuy($this->getCosts($player)));
  }

  function argsPlow()
  {
    $player = Players::getActive();
    $source = $this->getCtxArgs()['source'] ?? null;
    return [
      'zones' => $player->board()->getPlowableZones(),
      'source' => $source,
      'i18n' => ['source'],
      'descSuffix' => is_null($source) ? '' : 'source',
    ];
  }

  public function getCosts($player)
  {
    $costs = Utils::formatCost([]);
    $this->checkCostModifiers($costs, $player, []);
    return $costs;
  }

  function actPlow($field)
  {
    self::checkAction('actPlow');
    $player = Players::getActive();
    $source = $this->getCtxArgs()['source'] ?? null;
    $costs = $this->getCosts($player);
    // Sanity checks on pos
    if (!in_array($field, $player->board()->getPlowableZones())) {
      throw new \BgaVisibleSystemException('You can\'t plow a field here');
    }

    // Add them to board (update $pos variable to add info about the meeple)
    $player->board()->addField($field);

    // Notify
    Notifications::plow($player, $field, $source);

    // Pay and proceed
    $player->pay(1, $costs, clienttranslate('Plow'));

    $this->checkAfterListeners($player, ['field' => $field]);
    $this->resolveAction(['field' => $field]);
  }
}
