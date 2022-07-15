<?php
namespace CAV\States;
use CAV\Core\Globals;
use CAV\Core\Notifications;
use CAV\Core\Engine;
use CAV\Managers\Players;
use CAV\Managers\ActionCards;
use CAV\Managers\Buildings;
use CAV\Managers\Meeples;
use CAV\Managers\Dwarves;
use CAV\Managers\Actions;
use CAV\Helpers\Utils;

trait HarvestTrait
{
  /**
   * List of listeners for harvest
   * - BeforeHarvest : D98_Transactor
   *
   * - StartHarvest : D97_BeggingStudent
   *
   * - StartHarvestFieldPhase : B61_ThreeFieldRotation
   * - HarvestFieldPhase : A112_ScytheWorker, B39_Loom, B50_ButterChurn, C98_CubeCutter, D38_MilkingStool
   * - action REAP : C106_PotatoHarvester, C70_LettucePatch
   * - EndHarvestFieldPhase : C110_HomeBrewer
   *
   * - StartHarvestFeedingPhase : C107_Baker
   * - HarvestFeedingPhase : C59_SchnappsDistillery, D12_MilkingPlace, D84_FeedPellets
   * - EndHarvestFeedingPhase : C41_FarmStore
   *
   * - breeding phase : D115_FodderPlanter
   * - EndHarvest : C113_WinterCaretaker
   */

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
  // TODO : remove !!
  function stHarvestPrepareFeedListener()
  {
    $this->stStartHarvestFeedingPhase();
  }

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
    $costs = Utils::formatFee([FOOD => $player->getHarvestCost()]);
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

  // TODO : remove
  function stHarvestEndOfFeed()
  {
    $this->checkBuildingListeners('EndHarvestFeedingPhase', 'stHarvestPrepareBreed', [], \HARVEST);
  }

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
    if (Globals::getTurn() == 14) {
      foreach ($player->getCards(null, true) as $card) {
        $enforceReorganize = $enforceReorganize || $card->enforceReorganizeOnLastHarvest();
      }
    }

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
