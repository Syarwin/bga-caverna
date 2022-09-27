<?php
namespace CAV\States;
use CAV\Core\Globals;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Managers\Players;
use CAV\Managers\ActionCards;
use CAV\Managers\Buildings;
use CAV\Managers\Meeples;
use CAV\Managers\Dwarfs;
use CAV\Managers\Actions;
use CAV\Helpers\Utils;

trait HarvestTrait
{
  /****************************
   ****** Starting harvest *****
   ****************************/
  function stStartHarvest()
  {
    Globals::setHarvest(true);
    Globals::setSkipHarvest(Globals::getPassHarvest());
    Globals::setPassHarvest([]);
    Globals::setExchangeFlags([]);

    $harvestToken = Meeples::startHarvest();
    Notifications::startHarvest($harvestToken);

    if ($harvestToken['type'] == \HARVEST_1FOOD) {
      Globals::setHarvestCost(1);
      Globals::setHarvest(true);
      Notifications::updateHarvestCosts();
      $this->initCustomTurnOrder('harvestFeed', HARVEST, ST_HARVEST_FEED, 'stHarvestEnd');
    } elseif ($harvestToken['type'] == \HARVEST_CHOICE) {
      $this->initCustomTurnOrder('harvestChoice', HARVEST, 'stHarvestChoice', 'stStartHarvestFieldPhase');
    } elseif ($harvestToken['type'] == \HARVEST_NONE) {
      $this->gamestate->nextState('end');
    } else {
      $this->checkBuildingListeners('StartHarvest', 'stStartHarvestFieldPhase', [], HARVEST);
    }
  }

  function stHarvestChoice()
  {
    $player = Players::getActive();
    $reaction['childs'][] =
      // Inserting into engine
      self::giveExtraTime($player->getId());
    Engine::setup(
      [
        'action' => HARVEST_CHOICE,
        'pId' => $player->getId(),
      ],
      ['order' => 'harvestChoice']
    );
    Engine::proceed();
  }

  /****************************
   ********* Field phase *******
   ****************************/
  function stStartHarvestFieldPhase()
  {
    $this->checkBuildingListeners('StartHarvestFieldPhase', 'stInitHarvestFieldPhase', [], HARVEST);
  }

  function stInitHarvestFieldPhase()
  {
    $this->initCustomTurnOrder('harvestField', HARVEST, ST_HARVEST_FIELD, 'stEndHarvestFieldPhase');
  }

  /**
   * Harvest growing crops
   */
  function stHarvestFieldPhase()
  {
    $player = Players::getActive();
    $harvestChoice = Globals::getHarvestChoice();

    if (($harvestChoice[$player->getId()] ?? null) == BREED) {
      $this->nextPlayerCustomOrder('harvestField');
      return;
    }

    // Get reaction cards
    $event = [
      'type' => 'HarvestFieldPhase',
      'method' => 'HarvestFieldPhase',
      'pId' => $player->getId(),
    ];
    $reaction = Buildings::getReaction($event, false);

    // Insert default REAP node
    $crops = $player->board()->getHarvestCrops();
    if (count($crops) > 0) {
      $reaction['childs'][] = [
        'action' => REAP,
        'pId' => $player->getId(),
      ];
    }

    if (empty($reaction['childs'])) {
      $this->nextPlayerCustomOrder('harvestField');
    } else {
      Engine::setup($reaction, ['order' => 'harvestField']);
      Engine::proceed();
    }
  }

  function stEndHarvestFieldPhase()
  {
    $this->checkBuildingListeners('EndHarvestFieldPhase', 'stStartHarvestFeedingPhase', [], \HARVEST);
  }

  /****************************
   ******* Feeding phase *******
   ****************************/
  function stStartHarvestFeedingPhase()
  {
    $this->checkBuildingListeners('StartHarvestFeedingPhase', 'stInitHarvestFeedingPhase', [], \HARVEST);
  }

  function stInitHarvestFeedingPhase()
  {
    $this->initCustomTurnOrder('harvestFeed', \HARVEST, ST_HARVEST_FEED, 'stEndHarvestFeedingPhase');
  }

  /**
   * Go to next player that needs to feed its family
   */
  function stHarvestFeed()
  {
    $player = Players::getActive();
    // Get triggered cards
    $event = [
      'type' => 'HarvestFeedingPhase',
      'method' => 'HarvestFeedingPhase',
      'pId' => $player->getId(),
    ];
    $reaction = Buildings::getReaction($event, false);

    // Exchange node
    $costs = $player->getHarvestCost();
    Buildings::applyEffects($player, 'ComputeHarvestCosts', $costs);
    if (Actions::isDoable(EXCHANGE, [], $player)) {
      // Do we have to enter pay ?
      $pref = $player->getPref(OPTION_AUTOPAY_HARVEST);
      $cantPay = !Actions::isDoable(PAY, ['costs' => $costs], $player);
      $hasSpecialExchange = Actions::isDoable(EXCHANGE, ['exclusive' => true], $player);
      if ($pref != OPTION_AUTOPAY_HARVEST_ENABLED || $cantPay || $hasSpecialExchange) {
        $reaction['childs'][] = [
          'action' => EXCHANGE,
          'reusable' => true,
          'pId' => $player->getId(),
        ];
      }
    }

    // Pay node
    $reaction['childs'][] = [
      'action' => PAY,
      'pId' => $player->getId(),
      'resolveParent' => true,
      'args' => [
        'costs' => $costs,
        'source' => clienttranslate('Harvest'),
        'harvest' => true,
      ],
    ];

    // Inserting into engine
    self::giveExtraTime($player->getId());
    Engine::setup($reaction, ['order' => 'harvestFeed']);
    Engine::proceed();
  }

  // // TODO : remove
  // function stHarvestEndOfFeed()
  // {
  //   $this->checkBuildingListeners('EndHarvestFeedingPhase', 'stHarvestPrepareBreed', [], \HARVEST);
  // }

  function stEndHarvestFeedingPhase()
  {
    $this->checkBuildingListeners('EndHarvestFeedingPhase', 'stHarvestPrepareBreed', [], \HARVEST);
  }

  /****************************
   ******* Breeding phase ******
   ****************************/
  function stHarvestPrepareBreed()
  {
    $this->initCustomTurnOrder('harvestBreed', HARVEST, ST_HARVEST_BREED, 'stHarvestEnd');
  }

  /**
   * Go to next player that needs to feed its family
   */
  function stHarvestBreed()
  {
    $player = Players::getActive();
    $harvestChoice = Globals::getHarvestChoice();

    if (($harvestChoice[$player->getId()] ?? null) == FIELD) {
      $this->nextPlayerCustomOrder('harvestBreed');
      return;
    }

    // If player has enough to breed, creation of a baby in reserve
    if ($player->canBreed() || $player->hasRuby()) {
      Engine::setup(
        [
          'pId' => $player->getId(),
          'type' => \NODE_PARALLEL,
          'childs' => [
            [
              'action' => BREED,
              'args' => ['trigger' => HARVEST],
              'optional' => !$player->canBreed(),
            ],
          ],
        ],
        ['order' => 'harvestBreed']
      );
      Engine::proceed();
    } else {
      $this->nextPlayerCustomOrder('harvestBreed');
    }
  }

  /****************************
   ******* Ending harvest ******
   ****************************/
  function stHarvestEnd()
  {
    $this->checkBuildingListeners('EndHarvest', ST_END_OF_TURN, [], \HARVEST);
  }
}
