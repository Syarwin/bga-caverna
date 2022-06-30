<?php
namespace AGR\Actions;
use AGR\Core\Globals;
use AGR\Managers\Players;
use AGR\Managers\Meeples;
use AGR\Core\Notifications;
use AGR\Core\Engine;
use AGR\Helpers\Utils;

class PlaceFutureMeeples extends \AGR\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
  }

  public function getState()
  {
    return ST_PLACE_FUTURE_MEEPLES;
  }

// TODO: needs exception for "roomStone" and maybe other weird cases
  public function getDescription($ignoreResources = false)
  {
    $resources = $this->ctx->getArgs()['resources'];
    $goods = '';

    foreach ($resources as $resType => $amount) {
      $type = '<' . strtoupper($resType) . '>';
      $goods = $goods . $type . ", ";
    }
    $goods = substr($goods, 0, -2);

    return [
      'log' => clienttranslate('place ${resources_desc} for future rounds'),
      'args' => [
        'resources_desc' => $goods,
      ],
    ];
  }

  public function isAutomatic($player = null)
  {
    return true;
  }

  public function isIndependent($player = null)
  {
    return true;
  }

  public function stPlaceFutureMeeples()
  {
    $args = $this->ctx->getArgs();
    $resources = $args['resources'];
    $currentTurn = Globals::getTurn();

    // Compute the turns where we are going to add stuff, if any
    $turns = [];
    foreach ($args['turns'] as $turn) {
      if (!\is_int($turn)) {
        // If $x is not int, it's of the form +X
        $turn = (int) substr($turn, 1);
        $turn += $currentTurn;
      }

       if ($turn > $currentTurn && $turn <= 14) {
        $turns[] = $turn;
      }
    }

    if (empty($turns)) {
      $this->resolveAction();
      return;
    }

    $player = Players::get($args['pId']);
    $flagCard = $args['flagCard'] ?? false;
    $cardId = $args['cardId'] ?? null;

    // Create meeples and place them
    $meepleIds = [];
    foreach ($turns as $turn) {
      foreach ($resources as $resType => $amount) {
        array_push(
          $meepleIds,
          ...Meeples::createResourceInLocation(
            $resType,
            'turn_' . $turn,
            $player->getId(),
            $flagCard ? $cardId : null,
            null,
            $amount
          )
        );
      }
    }

    $meeples = Meeples::getMany($meepleIds);
    Notifications::placeMeeplesForFuture($player, $resources, $turns, $meeples);
    $this->resolveAction();
  }
}
