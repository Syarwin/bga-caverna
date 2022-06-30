<?php
namespace AGR\Actions;
use AGR\Managers\Meeples;
use AGR\Managers\Players;
use AGR\Managers\ActionCards;
use AGR\Core\Notifications;
use AGR\Helpers\Utils;

class ActivateCards extends \AGR\Models\Action
{
  public function getState()
  {
    return ST_ACTIVATECARDS;
  }

  public function stActivateCards()
  {
    $player = Players::getActive();
    $activeId = $player->getId();
    $args = $this->ctx->getArgs();
    $cardId = $args['cardId'];
    $turnCard = ActionCards::get($cardId)->getTurn();

    // TODO: manage start of phase cards with Args
    if ($args['action'] == 'placeFarmer') {
      $cardId = substr($cardId, 6, 99);
      for ($i = 0; $i < Players::count(); $i++) {
        foreach ($player->getPlayedCards() as $card) {
          $method = 'on' . ($actionCardsMap[$cardId] ?? $cardId);
          $card->$method($player);

          if (method_exists($card, 'onTurn' . $turnCard)) {
            $method = 'onTurn' . $turnCard;
            $card->$method($player);
          }
        }

        $player = Players::get(Players::getNextId($player));
      }
    }

    $this->resolveAction();
  }

  public function getDescription()
  {
    return [
      'log' => clienttranslate('Gain ${resources_desc}'),
      'args' => [
        'resources_desc' => Utils::resourcesToStr($this->ctx->getArgs()),
      ],
    ];
  }
}
