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
    Notifications::startHarvest();
    Globals::setHarvest(true);
    Globals::setSkipHarvest(Globals::getPassHarvest());
    Globals::setPassHarvest([]);
    Globals::setExchangeFlags([]);

    $this->checkBuildingListeners('StartHarvest', 'stStartHarvestFieldPhase', [], HARVEST);
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
    Buildings::applyEffects($player, 'ComputeHarvestCosts', $costs['costs']);
    if (Actions::isDoable(EXCHANGE, [], $player)) {
      // Do we have to enter pay ?
      $pref = $player->getPref(OPTION_AUTOPAY_HARVEST);
      $cantPay = !Actions::isDoable(PAY, $costs, $player);
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
      'args' => $costs,
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
    // Listen for cards enforcing reorganization on last harvest (eg Organic Farmer)
    $enforceReorganize = false;
    // if (Globals::getTurn() == 12) {
    //   foreach ($player->getBuildings(null, true) as $card) {
    //     $enforceReorganize = $enforceReorganize || $card->enforceReorganizeOnLastHarvest();
    //   }
    // }

    // If player has enough to breed, creation of a baby in reserve
    $created = $player->breed();
    if (!$created && !$enforceReorganize) {
      $this->nextPlayerCustomOrder('harvestBreed');
      return;
    }

    // Inserting leaf REORGANIZE
    Engine::setup(
      [
        'pId' => $player->getId(),
        'action' => REORGANIZE,
        'args' => [
          'trigger' => HARVEST,
        ],
      ],
      ['order' => 'harvestBreed']
    );
    Engine::proceed();
  }

  /****************************
   ******* Ending harvest ******
   ****************************/
  function stHarvestEnd()
  {
    $this->checkBuildingListeners('EndHarvest', ST_END_OF_TURN, [], \HARVEST);
  }
}
