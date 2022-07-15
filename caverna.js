/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * caverna implementation : © Timothée Pecatte <tim.pecatte@gmail.com>, Vincent Toper <vincent.toper@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * caverna.js
 *
 * caverna user interface script
 *
 * In this file, you are describing the logic of your user interface, in Javascript language.
 *
 */

var isDebug = window.location.host == 'studio.boardgamearena.com' || window.location.hash.indexOf('debug') > -1;
var debug = isDebug ? console.info.bind(window.console) : function () {};

define([
  'dojo',
  'dojo/_base/declare',
  g_gamethemeurl + 'modules/js/vendor/nouislider.min.js',
  g_gamethemeurl + 'modules/js/vendor/sortable.min.js',
  'ebg/core/gamegui',
  'ebg/counter',
  g_gamethemeurl + 'modules/js/Core/game.js',
  g_gamethemeurl + 'modules/js/Core/modal.js',
  g_gamethemeurl + 'modules/js/ActionCards.js',
  g_gamethemeurl + 'modules/js/Players.js',
  g_gamethemeurl + 'modules/js/PlayerBoard.js',
  g_gamethemeurl + 'modules/js/Meeples.js',
  g_gamethemeurl + 'modules/js/Cards.js',
  g_gamethemeurl + 'modules/js/States/Sow.js',
  g_gamethemeurl + 'modules/js/States/ReorganizeAnimals.js',
  g_gamethemeurl + 'modules/js/States/Exchange.js',
  g_gamethemeurl + 'modules/js/States/SpecialEffect.js',
], function (dojo, declare, noUiSlider, sortable) {
  const COLORBLIND = 104;
  const FONT_DOMINICAN = 105;
  const PLAYER_BOARDS = 107;
  const HAND_CARDS = 108;
  const PLAYER_RESOURCES = 109;

  return declare(
    'bgagame.caverna',
    [
      customgame.game,
      caverna.actionCards,
      caverna.players,
      caverna.playerBoard,
      caverna.meeples,
      caverna.sow,
      caverna.cards,
      caverna.reorganize,
      caverna.exchange,
      caverna.specialEffect,
    ],
    {
      constructor() {
        this._activeStates = [
          'construct',
          'exchange',
          'fencing',
          'improvement',
          'occupation',
          'payResources',
          'placeFarmer',
          'plow',
          'reorganize',
          'sow',
          'stables',
          'resolveChoice',
          'confirmTurn',
          'confirmPartialTurn',
          'renovation',
        ];
        this._notifications = [
          ['revealActionCard', 1100],
          ['placeFarmer', null],
          ['addFences', null],
          ['addStables', null],
          ['collectResources', null],
          ['gainResources', null],
          ['payResources', null],
          ['accumulation', null],
          ['growFamily', null],
          ['growChildren', 1000],
          ['firstPlayer', 800],
          ['plow', 1000],
          ['sow', null],
          ['construct', 1000],
          ['renovate', 1000],
          ['returnHome', null],
          ['updateDropZones', 1],
          ['reorganize', null],
          ['harvestCrop', null],
          ['exchange', null],
          ['placeMeeplesForFuture', null],
          ['buyCard', null],
          ['buyAndPassCard', null],
          ['buyAndDestroyCard', null],
          ['payWithCard', 1000],
          ['updateScores', 1],
          ['silentKill', null],
          ['silentDestroy', 1],
          ['clearTurn', 1],
          ['refreshUI', 1],
          ['refreshHand', 1],
          ['startHarvest', 3200],
          ['addCardToDraftSelection', 600],
          ['removeCardFromDraftSelection', 600],
          ['confirmDraftSelection', 1],
          ['clearDraftPools', 1000],
          ['draftIsOver', 500],
          ['seed', 10],
          ['updateHarvestCosts', 1],
        ];

        this._canReorganize = false;
        this._isDraft = false;
        this._cardStorage = {};

        // Fix mobile viewport (remove CSS zoom)
        this.default_viewport = 'width=1000';

        this._cardScale = this.getConfig('cavernaCardScale', 80);
        this._cardAnimationSpeed = this.getConfig('cavernaCardAnimationSpeed', 80);
        this._centralBoardScale = this.getConfig('cavernaCentralBoardScale', 100);
        this._playerBoardScale = this.getConfig('cavernaPlayerBoardScale', 100);
      },

      /**
       * Setup:
       *	This method set up the game user interface according to current game situation specified in parameters
       *	The method is called each time the game interface is displayed to a player, ie: when the game starts and when a player refreshes the game page (F5)
       *
       * Params :
       *	- mixed gamedatas : contains all datas retrieved by the getAllDatas PHP method.
       */
      setup(gamedatas) {
        debug('SETUP', gamedatas);
        // Create a new div for "anytime" buttons
        dojo.place(
          "<div id='anytimeActions' style='display:inline-block;float:right'></div>",
          $('generalactions'),
          'after',
        );

        // 3D ribbon
        dojo.place('<div id="page-title-left-ribbon" class="ribbon-effect"></div>', 'page-title');
        dojo.place('<div id="page-title-right-ribbon" class="ribbon-effect"></div>', 'page-title');

        // // Add Harvest Icons
        // [4, 7, 9, 11, 13, 14].forEach((turn) => {
        //   if (turn >= gamedatas.turn) {
        //     dojo.place(`<div id="harvest-${turn}" class="harvest-icon"></div>`, 'harvest-slot-' + turn);
        //     this.addCustomTooltip('harvest-' + turn, _('Harvest will take place at the end of that turn'));
        //   }
        // });
        //
        // dojo.attr('game_play_area', 'data-additional', gamedatas.isAdditional ? 1 : 0);
        // dojo.attr('game_play_area', 'data-turn', gamedatas.turn);
        // this.setupInfoPanel();
        // this.setupScoresModal();
        // this.setupActionCards();
        // this.setupPlayers();
        // this.setupBuildings();
        // this.setupMeeples();
        // this.setupAnimalsDropZones();
        // this.updatePrefs();
        // this.setCardScale(this._cardScale);
        // this.setCardAnimationSpeed(this._cardAnimationSpeed);
        // this.setCentralBoardScale(this._centralBoardScale);
        // this.setPlayerBoardScale(this._playerBoardScale);
        // if (gamedatas.seed != false) this.showSeed(gamedatas.seed);

        this.inherited(arguments);
      },

      onLoadingComplete() {
        // TODO
        // if (localStorage.getItem('cavernaTour') != 1) {
        //   if (!this.isReadOnly) this.showTour();
        // } else {
        //   dojo.style('tour-slide-footer', 'display', 'none');
        //   $('neverShowMe').checked = true;
        // }

        this.inherited(arguments);
      },

      onPreferenceChange(pref, value) {
        this.updatePrefs();
        if (pref == PLAYER_BOARDS || pref == HAND_CARDS) {
          this.updateHandContainer();
          this.onScreenWidthChange();
        }
        if (pref == PLAYER_RESOURCES) {
          this.updateResourceBarsPositions();
        }
      },

      updatePrefs() {
        dojo.toggleClass('ebd-body', 'colorblind', this.prefs[COLORBLIND].value == 1);
        dojo.toggleClass('ebd-body', 'disable-dominican', this.prefs[FONT_DOMINICAN].value == 1);
        this.onScreenWidthChange();
      },

      onScreenWidthChange() {
        // if (this.prefs[HAND_CARDS].value != 0 && this.prefs[PLAYER_BOARDS].value == 1) {
        //   dojo.style('player-boards', 'min-height', $('player-boards-left-column').offsetHeight + 'px');
        // }
        //
        // dojo.toggleClass('player-boards', 'player-boards-right', this.prefs[PLAYER_BOARDS].value == 1);
        // if (this.prefs[PLAYER_BOARDS].value == 1) {
        //   let gamePlaySize = $('game_play_area').offsetWidth;
        //   let playerBoard = document.querySelector('.player-board-wrapper');
        //   if (playerBoard == null) return;
        //   let playerBoardSize = playerBoard.offsetWidth + playerBoard.offsetLeft;
        //   dojo.toggleClass('player-boards', 'player-boards-right', playerBoardSize < gamePlaySize);
        // }
      },

      notif_clearTurn(n) {
        debug('Notif: restarting turn', n);
        this.cancelLogs(n.args.notifIds);
      },

      notif_refreshUI(n) {
        debug('Notif: refreshing UI', n);
        ['meeples', 'players', 'scores', 'playerCards'].forEach((value) => {
          this.gamedatas[value] = n.args.datas[value];
        });
        this.setupMeeples();
        this.setupAnimalsDropZones();
        this.updateBuildings();
        this.updatePlayersCounters();
        this.updatePlayersScores();
      },

      notif_refreshHand(n) {
        debug('Notif: refreshing UI', n);
        this.gamedatas.players[n.args.player_id].hand = n.args.hand;
        this.updateHandCards();
      },

      notif_startHarvest(n) {
        debug('Notif: starting harvest', n);

        let elem = $('harvest-' + n.args.turn);
        dojo.empty('card-overlay');
        if (this.isFastMode()) {
          dojo.destroy(elem);
          return;
        }

        dojo.place(elem, 'card-overlay');

        // Start animation
        this.slide(elem, 'card-overlay', {
          from: 'harvest-slot-' + n.args.turn,
        });
        dojo.addClass('card-overlay', 'active');
        dojo.style(elem, 'transform', `scale(5)`);

        setTimeout(() => dojo.removeClass('card-overlay', 'active'), 1800);
        setTimeout(() => dojo.destroy(elem), 3000);
      },

      clearPossible() {
        this.clearSowRadios();
        this.disableAnimalControls();
        dojo.empty('anytimeActions');
        dojo.query('.player-board-wrapper.current.harvest').removeClass('harvest');
        dojo.query('.phantom').removeClass('.phantom');
        this._isHarvest = false;
        if (this._exchangeDialog) this._exchangeDialog.hide();
        if (this._showSeedDialog) this._showSeedDialog.destroy();
        this._majorsDialog.hide();
        this.inherited(arguments);
      },

      onEnteringState(stateName, args) {
        debug('Entering state: ' + stateName, args);
        if (this.isFastMode()) return;

        if (stateName == 'exchange' && args.args && args.args.automaticAction) {
          args.args.descSuffix = 'cook';
        }

        if (args.args && args.args.descSuffix) {
          this.changePageTitle(args.args.descSuffix);
        }

        if (args.args && args.args.optionalAction) {
          let base = args.args.descSuffix ? args.args.descSuffix : '';
          this.changePageTitle(base + 'skippable');
        }

        if (args.args && args.args.source) {
          if (this.gamedatas.gamestate.descriptionmyturn.search('{source}') === -1) {
            $('pagemaintitletext').appendChild(document.createTextNode(` (${_(args.args.source)})`));
          }
        }

        if (this._activeStates.includes(stateName) && !this.isCurrentPlayerActive()) return;

        if (args.args && args.args.optionalAction && !args.args.automaticAction) {
          this.addSecondaryActionButton('btnPassAction', _('Pass'), () => this.takeAction('actPassOptionalAction'));
        }

        // Restart turn button
        if (
          args.args &&
          args.args.previousEngineChoices &&
          args.args.previousEngineChoices >= 1 &&
          !args.args.automaticAction
        ) {
          this.addDangerActionButton('btnRestartTurn', _('Restart turn'), () => {
            this.stopActionTimer();
            this.takeAction('actRestart');
          });
        }

        if (this.isCurrentPlayerActive() && args.args) {
          // Anytime buttons
          if (args.args.anytimeActions) {
            args.args.anytimeActions.forEach((action, i) => {
              let msg = action.desc;
              msg = msg.log ? this.format_string_recursive(msg.log, msg.args) : _(msg);
              msg = this.formatStringMeeples(msg);

              this.addPrimaryActionButton(
                'btnAnytimeAction' + i,
                msg,
                () => this.takeAction('actAnytimeAction', { id: i }, false),
                'anytimeActions',
              );
            });
          }
        }

        // Call appropriate method
        var methodName = 'onEnteringState' + stateName.charAt(0).toUpperCase() + stateName.slice(1);
        if (this[methodName] !== undefined) this[methodName](args.args);
      },

      addActionChoiceBtn(choice, disabled = false) {
        if ($('btnChoice' + choice.id)) return;

        let desc =
          typeof choice.description == 'string'
            ? _(choice.description)
            : this.format_string_recursive(_(choice.description.log), choice.description.args);

        this.addSecondaryActionButton(
          'btnChoice' + choice.id,
          desc,
          disabled ? () => {} : () => this.takeAction('actChooseAction', { id: choice.id }),
        );
        if (disabled) {
          dojo.addClass('btnChoice' + choice.id, 'disabled');
        }
      },

      onEnteringStateResolveChoice(args) {
        Object.values(args.choices).forEach((choice) => this.addActionChoiceBtn(choice, false));
        Object.values(args.allChoices).forEach((choice) => this.addActionChoiceBtn(choice, true));
      },

      onEnteringStateImpossibleAction(args) {
        this.addActionChoiceBtn(
          {
            choiceId: 0,
            description: args.desc,
          },
          true,
        );
      },

      addConfirmTurn(args, action) {
        this.addPrimaryActionButton('btnConfirmTurn', _('Confirm'), () => {
          this.stopActionTimer();
          this.takeAction(action);
        });

        const OPTION_CONFIRM = 103;
        let n = args.previousEngineChoices;
        let timer = Math.min(10 + 2 * n, 20);
        this.startActionTimer('btnConfirmTurn', timer, this.prefs[OPTION_CONFIRM].value);
      },

      onEnteringStateConfirmTurn(args) {
        this.addConfirmTurn(args, 'actConfirmTurn');
      },

      onEnteringStateConfirmPartialTurn(args) {
        this.addConfirmTurn(args, 'actConfirmPartialTurn');
      },

      /************************
       **** ATOMIC ACTIONS *****
       ************************/
      onEnteringStatePlaceFarmer(args) {
        this.promptActionCard(args.cards, (cId) => this.takeAtomicAction('actPlaceFarmer', [cId]), args.allCards);
      },

      onEnteringStateFencing(args) {
        this.promptPlayerBoardZones(args.zones, 1, args.max, (zones) => this.takeAtomicAction('actFence', [zones]));
      },

      onEnteringStatePlow(args) {
        this.promptPlayerBoardZones(args.zones, 1, null, (zone) => this.takeAtomicAction('actPlow', [zone]));
      },

      onEnteringStateConstruct(args) {
        this.promptPlayerBoardZones(args.zones, 1, args.max, (zones) => this.takeAtomicAction('actConstruct', [zones]));
      },

      onEnteringStateStables(args) {
        this.promptPlayerBoardZones(args.zones, 1, args.max, (zones) => this.takeAtomicAction('actStables', [zones]));
      },

      onEnteringStateImprovement(args) {
        if (args._private.cards.length != 0)
          this.promptCard(args.types, args._private.cards, (cardId) =>
            this.takeAtomicAction('actImprovement', [cardId]),
          );
      },

      onEnteringStateOccupation(args) {
        this.promptCard(args.types, args._private.cards, (cardId) => this.takeAtomicAction('actOccupation', [cardId]));
      },

      onEnteringStatePayResources(args) {
        if (args.combinations.length == 1) {
          return;
        }

        args.combinations.forEach((cost, i) => {
          // Compute desc
          let log = '',
            arg = {};
          if (cost.card == undefined) {
            if (cost.sources && cost.sources.length) {
              log = _('Pay ${resource} (${cards})');
              arg.resource = this.formatResourceArray(cost);
              arg.cards = cost.sources.map((cardId) => _(args.cardNames[cardId])).join(', ');
            } else {
              log = _('Pay ${resource}');
              arg.resource = this.formatResourceArray(cost);
            }
          } else {
            log = _('Return ${card}');
            arg.card = _(args.cardNames[cost.card]);
          }
          let desc = this.format_string_recursive(log, arg);

          // Add button
          this.addSecondaryActionButton('btnChoicePay' + i, desc, () => this.takeAtomicAction('actPay', [cost]));
        });
      },

      onEnteringStateRenovation(args) {
        if (args.combinations.length == 1) {
          return;
        }

        args.combinations.forEach((cost, i) => {
          // Compute desc
          let log = '',
            arg = {};
          if (cost == 'roomClay') {
            log = _('Renovate to clay');
          } else if (cost == 'roomStone') {
            log = _('Renovate to stone');
          }

          // Add button
          this.addSecondaryActionButton('btnChoiceRenovate' + i, log, () =>
            this.takeAtomicAction('actRenovation', [cost]),
          );
        });
      },

      // Generic call for Atomic Action that encode args as a JSON to be decoded by backend
      takeAtomicAction(action, args) {
        if (!this.checkAction(action)) return false;

        this.takeAction('actTakeAtomicAction', { actionArgs: JSON.stringify(args) }, false);
      },

      /************************
       ******* SETTINGS ********
       ************************/
      setupInfoPanel() {
        dojo.place(
          this.format_string(jstpl_configPlayerBoard, {
            centralBoardSize: _('Size of central board(s)'),
            playerBoardSize: _('Size of player board(s)'),
            cardSize: _('Size of cards'),
            playCard: _('Speed of animation when a card is played'),
          }),
          'player_boards',
          'first',
        );
        dojo.connect($('show-settings'), 'onclick', () => this.toggleSettings());
        this.addTooltip('show-settings', '', _('Display some settings about the game.'));

        let chk = $('help-mode-chk');
        dojo.connect(chk, 'onchange', () => this.toggleHelpMode(chk.checked));
        this.addTooltip('help-mode-switch', '', _('Toggle help/safe mode.'));
        this.setupSettings();
        this.setupHelper();
        this.setupTour();
      },

      // updatePlayerOrdering() {
      //   this.inherited(arguments);
      //   dojo.place('player_board_config', 'player_boards', 'first');
      // },

      toggleSettings() {
        dojo.toggleClass('settings-controls-container', 'settingsControlsHidden');

        // Hacking BGA framework
        if (dojo.hasClass('ebd-body', 'mobile_version')) {
          dojo.query('.player-board').forEach((elt) => {
            if (elt.style.height != 'auto') {
              dojo.style(elt, 'min-height', elt.style.height);
              elt.style.height = 'auto';
            }
          });
        }
      },

      setupSettings() {
        dojo.place($('preference_control_103').parentNode.parentNode, 'settings-controls-container');
        dojo.place($('preference_control_107').parentNode.parentNode, 'settings-controls-container');
        dojo.place($('preference_control_108').parentNode.parentNode, 'settings-controls-container');
        dojo.place($('preference_control_109').parentNode.parentNode, 'settings-controls-container');

        dojo.place($('preference_control_102').parentNode.parentNode, 'settings-controls-container');
        dojo.place($('preference_control_106').parentNode.parentNode, 'settings-controls-container');
        dojo.place($('preference_control_104').parentNode.parentNode, 'settings-controls-container');
        dojo.place($('preference_control_105').parentNode.parentNode, 'settings-controls-container');

        this._cardScaleSlider = document.getElementById('layout-control-card-size');
        noUiSlider.create(this._cardScaleSlider, {
          start: [this._cardScale],
          step: 5,
          padding: 10,
          range: {
            min: [40],
            max: [120],
          },
        });
        this._cardScaleSlider.noUiSlider.on('slide', (arg) => this.setCardScale(parseInt(arg[0])));

        this._cardAnimationSpeedSlider = document.getElementById('layout-control-card-animation-speed');
        noUiSlider.create(this._cardAnimationSpeedSlider, {
          start: [this._cardAnimationSpeed],
          step: 5,
          padding: 10,
          range: {
            min: [15],
            max: [200],
          },
        });
        this._cardAnimationSpeedSlider.noUiSlider.on('slide', (arg) => this.setCardAnimationSpeed(parseInt(arg[0])));

        this._centralBoardScaleSlider = document.getElementById('layout-control-central-board-size');
        noUiSlider.create(this._centralBoardScaleSlider, {
          start: [this._centralBoardScale],
          step: 5,
          padding: 10,
          range: {
            min: [60],
            max: [140],
          },
        });
        this._centralBoardScaleSlider.noUiSlider.on('slide', (arg) => this.setCentralBoardScale(parseInt(arg[0])));

        this._playerBoardScaleSlider = document.getElementById('layout-control-player-board-size');
        noUiSlider.create(this._playerBoardScaleSlider, {
          start: [this._playerBoardScale],
          step: 5,
          padding: 10,
          range: {
            min: [40],
            max: [140],
          },
        });
        this._playerBoardScaleSlider.noUiSlider.on('slide', (arg) => this.setPlayerBoardScale(parseInt(arg[0])));
      },

      setCardScale(scale) {
        this._cardScale = scale;

        let applyScale = (elt, scale) => {
          elt.style.setProperty('--cavernaCardWidth', (235 * scale) / 100 + 'px');
          elt.style.setProperty('--cavernaCardHeight', (374 * scale) / 100 + 'px');
          elt.style.setProperty('--cavernaCardScale', scale / 100);
        };

        applyScale(document.documentElement, scale);
        document.querySelectorAll('.player-board-wrapper').forEach((board) => applyScale(board, 0.8 * scale));

        localStorage.setItem('cavernaCardScale', scale);
      },

      setCardAnimationSpeed(speed) {
        this._cardAnimationSpeed = speed;
        localStorage.setItem('cavernaCardAnimationSpeed', speed);
      },

      setCentralBoardScale(scale) {
        this._centralBoardScale = scale;
        document.documentElement.style.setProperty('--cavernaCentralBoardScale', scale / 100);
        localStorage.setItem('cavernaCentralBoardScale', scale);
      },

      setPlayerBoardScale(scale) {
        this._playerBoardScale = scale;
        document.documentElement.style.setProperty('--cavernaPlayerBoardScale', scale / 100);
        localStorage.setItem('cavernaPlayerBoardScale', scale);
        this.updatePlayerBoardDimensions();
      },

      updatePlayerBoardDimensions(pId = null) {
        let ids = pId == null ? Object.keys(this.gamedatas.players) : [pId];
        ids.forEach((pId) => {
          let holder = $('board-wrapper-' + pId);
          dojo.style('player-board-resizable-' + pId, {
            width: (holder.offsetWidth * this._playerBoardScale) / 100 + 'px',
            height: (holder.offsetHeight * this._playerBoardScale) / 100 + 'px',
          });
        });
      },

      /*
       * Display a helper with global scoring
       */
      setupHelper() {
        this._helperModal = new customgame.modal('showHelpsheet', {
          class: 'caverna_popin',
          closeIcon: 'fa-times',
          openAnimation: true,
          openAnimationTarget: 'show-help',
          title: _('Help sheet'),
          closeAction: 'hide',
          verticalAlign: 'flex-start',
        });

        dojo.connect($('show-help'), 'onclick', () => this.showHelper());
        this.addTooltip('show-help', '', _('Show scoring helpsheet.'));
      },

      showHelper() {
        this._helperModal.show();
      },

      /********************
       ***** LOAD SEED *****
       ********************/
      onEnteringStateLoadSeed() {
        // Create modal
        this._showSeedDialog = new customgame.modal('showSeedPrompt', {
          autoShow: true,
          class: 'caverna_popin',
          closeIcon: 'fa-times',
          title: _('Please enter a seed'),
          closeAction: 'hide',
          contents: `
            <div id="seed-form-container">
              <textarea id="seed-form-input"></textarea>
            </div>
            <div id="seed-dialog-footer"></div>
          `,
        });

        this.addPrimaryActionButton('btnShowSeedPrompt', _('Show form'), () => dialog.show());
        this.addPrimaryActionButton(
          'btnConfirmSeedPrompt',
          _('Confirm'),
          () => this.onConfirmSeed(),
          'seed-dialog-footer',
        );
      },

      onConfirmSeed() {
        let seed = $('seed-form-input').value;
        this.takeAction('actLoadSeed', { seed: JSON.stringify(seed) });
      },

      notif_seed(n) {
        debug('Notif: receiving seed', n);
        this.showSeed(n.args.seed);
      },

      showSeed(seed) {
        dojo.place('<div id="game-seed"></div>', 'anytimeActions', 'after');
        $('game-seed').innerHTML = dojo.string.substitute(
          _('Want to play with same configuration ? Here is the seed of your game : <code>${seed}</code>'),
          { seed },
        );
        dojo.style('game-seed', {
          display: 'inline-block',
          width: '100%',
          textAlign: 'center',
          fontSize: '80%',
          float: 'right',
        });
      },

      /********************
       ***** TOUR *****
       ********************/

      /*
       * Display an helper tour
       */
      setupTour() {
        this._tourModal = new customgame.modal('showTour', {
          class: 'caverna_popin',
          closeIcon: 'fa-times',
          openAnimation: true,
          openAnimationTarget: 'uwe-help',
          title: _('Agricola Tour'),
          contents: this.tplTourContent(),
          closeAction: 'hide',
          verticalAlign: 'flex-start',
        });

        dojo.connect($('uwe-help'), 'onclick', () => this.showTour());
        this.addTooltip('uwe-help', '', _('Show help tour.'));

        dojo.query('#tour-slider-container .tour-link').forEach((elt) => {
          let href = elt.getAttribute('href');
          dojo.connect(elt, 'click', () => this.setTourSlide(href));
        });

        dojo.connect($('neverShowMe'), 'change', function () {
          localStorage.setItem('cavernaTour', this.checked ? 1 : 0);
        });
      },

      showTour() {
        this._tourModal.show();
        this.setTourSlide('intro');
      },

      setTourSlide(link) {
        dojo.query('#tour-slider-container .slide').addClass('inactive');
        dojo.removeClass('tour-slide-' + link, 'inactive');
      },

      tplTourContent() {
        let nextBtn = (link, text = null) =>
          `<div class='tour-btn'><button href="${link}" class="action-button bgabutton bgabutton_blue tour-link">${
            text == null ? _('Next') : text
          }</button></div>`;

        let introBubble = _(
          "Welcome to Agricola on BGA. I'm here to give you a tour of the interface, to make sure you'll enjoy your games to the fullest.",
        );
        let introSectionUI = _('Global interface overview');
        let introSectionScoring = _('Scoring');
        let introSectionCards = _('Cards FAQ');
        let introSectionBugs = _('Report a bug');

        let panelInfoBubble = _("Let's start with this panel next to your name: a very handy toolbox.");
        let panelInfoItems = [
          _('my face: open this tour if you need it later'),
          _(
            "the switch will allow to toggle the safe/help mode: when enabled, clicking will do nothing, and instead will open tooltips on any elements with a question mark on it, making it sure you won't misclick",
          ),
          _('the star calendar: details of live scores (only if corresponding game option was enabled)'),
          _('the star: a breakdown of the endgame scoring'),
          _(
            "settings: this implementation comes with a lot of ways to customize your experience to your needs. Take some time to play with them until you're comfortable",
          ),
        ];

        let centralBoardBubble = _(
          'This is the central board where you take your actions. It consists of these parts:',
        );
        let centralBoardItems = [
          _(
            'Primary action spaces: available at the start of the game, the left column may vary or disappear based on the number of players',
          ),
          _(
            'Action space cards: one card is revealed at the start of each round. They are organized in 6 stages, randomized within each stage (the question mark on the round numbers preview the possible action space cards.) Each stage ends with a harvest:',
          ),
          _(
            'Additional tile: this is only present if you enable the corresponding game option. It provides additional actions that are linked together: if a player places a farmer on one of the actions, all the other linked actions also become unavailable.',
          ),
          _(
            'You may notice this section is adapted to be tablet/mobile-friendly (less wide than real components). It is publisher-approved and affected cards will be adapted or omitted.',
          ),
        ];

        let cardsBtnBubble = _('Where are all the cards?');
        let cardsBtnItems = [
          _('Clicking the Fireplace icon brings up the Major Improvements board.'),
          _(
            'The other icon (with mixed card types) brings up your hand of Occupations and Minor Improvements. It will not appear if you are in Beginner Mode, or if you have customized the setting to move it elsewhere.',
          ),
        ];

        let playerBoardBubble = _(
          'This is your personal farmyard board. Here you can build rooms, build fences, build stables, or plow fields.',
        );
        let playerPanelBubble = _('These player panels contain a lot of useful information!');
        let playerPanelItems = [
          _('Top section: resources and food in personal supply.'),
          _('Middle section: animals currently on farm, and actions remaining.'),
          _(
            'Bottom section: a reminder that each player can only have up to 5 farmers, 15 fence pieces, and 4 stables!',
          ),
          _(
            'The number after the / in your food reserve is a reminder of how much food the player currently needs for the next harvest.',
          ),
        ];

        let cookBubble = _(
          'In Agricola, cooking animals and seed can be a crucial step to provide the food you need to feed your people.',
        );
        let cookItems = [
          _(
            'All resource conversions happens in this window: just click on the exchanges you want to make before hitting "Confirm".',
          ),
          _(
            'If you need to make an exchange, just click on this button to open the previous modal (button is only present if you can do at least one exchange).',
          ),
        ];

        let reorganizeBubble = _(
          'Whenever you obtain animals, you must immediately accomodate them in your farm, otherwise they will be discarded (or cooked if you have the proper improvement.)',
        );
        let reorganizeItems = [
          _(
            'Most of the time, the game will automatically accommodate your animals in the best spot ever. However, you may also arrange them manually by either changing the "automatic" setting or by clicking this button:',
          ),
          _(
            'Click on the small arrows that pop up in your pastures, stables, and rooms to choose how your animals should be arranged.',
          ),
          _(
            'These controls will clear all, decrease, increase, or maximize a specific animal type in each of your pastures/stables/rooms.',
          ),
        ];

        let bugBubble = _('No code is error-free. Before reporting a bug, please follow the following steps.');
        let bugItems = [
          _(
            'If the issue is related to a card, please use the unique identifier (deck + number, e.g. A001) instead of the name.',
          ),
          _(
            'If your language is not English, please check the English card description using the unique identifier. If there is an incorrect translation to your language, please do not report a bug and use the translation module (Community > Translation) to fix it directly.',
          ),
          _(
            'For rules-related bugs, please double-check the rulebook before reporting to make sure you have it correct. The majority of rules-related bugs submitted so far... have not actually been bugs :/',
          ),
          _(
            'When you encounter a bug, please refresh your page to see if the bug goes away. Knowing whether or not a bug is persisting through refresh or not will help us find and fix it, so please include that in the bug report!',
          ),
        ];
        let bugReport = _('Report a new bug');

        let neverShowMe = _('Never show me this tour again');

        var bugUrl = this.metasiteurl + '/bug?id=0&table=' + this.table_id;

        return `
            <div id="tour-slider-container">
              <div id="tour-slide-intro" class="slide">
                <div class="bubble">${introBubble}</div>
                  <button href="panelInfo" class="action-button bgabutton bgabutton_blue tour-link">${introSectionUI}</button>
                  <button href="bugs" class="action-button bgabutton bgabutton_red tour-link">${introSectionBugs}</button>
                </ul>
              </div>

              <div id="tour-slide-panelInfo" class="slide">
                <div class="bubble">${panelInfoBubble}</div>
                <div class="split-hor">
                  <div>
                    <div id="img-panelInfo" class="tour-img"></div>
                  </div>
                  <div>
                    <ul>
                      <li>${panelInfoItems[0]}</li>
                      <li>${panelInfoItems[1]}</li>
                      <li>${panelInfoItems[2]}</li>
                      <li>${panelInfoItems[3]}</li>
                      <li>${panelInfoItems[4]}</li>
                    </ul>
                  </div>
                </div>
                ${nextBtn('centralBoard')}
              </div>

              <div id="tour-slide-centralBoard" class="slide">
                <div class="bubble">${centralBoardBubble}</div>
                <div class="split-hor">
                  <div>
                    <div class="tour-img" id="img-centralBoard"></div>
                  </div>
                  <div>
                    <ul>
                      <li>${centralBoardItems[0]}</li>
                      <li>
                        ${centralBoardItems[1]}
                        <div class="tour-img" id="img-harvest"></div>
                      </li>
                      <li>${centralBoardItems[2]}</li>
                    </ul>
                  </div>
                </div>
                <div class="tour-remark">${centralBoardItems[3]}</div>
                ${nextBtn('cardsBtn')}
              </div>

              <div id="tour-slide-cardsBtn" class="slide">
                <div class="bubble">${cardsBtnBubble}</div>
                <div class="split-hor">
                  <div>
                    <ul>
                      <li>${cardsBtnItems[0]}</li>
                      <li>${cardsBtnItems[1]}</li>
                    </ul>
                  </div>
                  <div>
                    <div class="tour-img" id="img-cardsBtn"></div>
                  </div>
                </div>
                ${nextBtn('boardPanel')}
              </div>


              <div id="tour-slide-boardPanel" class="slide">
                <div class="bubble">${playerBoardBubble}</div>
                <div class="tour-img" id="img-player-board"></div>

                <div class="split-hor">
                  <div>
                    <div class="tour-img" id="img-player-panel"></div>
                  </div>
                  <div>
                    <div class="bubble">${playerPanelBubble}</div>
                    <ul>
                      <li>${playerPanelItems[0]}</li>
                      <li>${playerPanelItems[1]}</li>
                      <li>${playerPanelItems[2]}</li>
                    </ul>
                  </div>
                </div>
                <div class="tour-remark">${playerPanelItems[3]}</div>

                ${nextBtn('cook')}
              </div>


              <div id="tour-slide-cook" class="slide">
                <div class="bubble">${cookBubble}</div>

                <div class="split-hor">
                  <div>
                    ${cookItems[0]}
                  </div>
                  <div>
                    <div class="tour-img" id="img-cook"></div>
                  </div>
                </div>

                <div class="tour-remark">
                  ${cookItems[1]}

                  <div class="tour-img" id="img-cook-btn"></div>
                </div>

                ${nextBtn('reorganize')}
              </div>


              <div id="tour-slide-reorganize" class="slide">
                <div class="bubble">${reorganizeBubble}</div>

                <div class="tour-remark">
                  ${reorganizeItems[0]}

                  <div class="tour-img" id="img-reorganize-btn"></div>
                </div>

                <div class="split-hor centered">
                  <div>
                    ${reorganizeItems[1]}
                  </div>
                  <div>
                    <div class="tour-img" id="img-reorganize"></div>
                  </div>
                </div>

                <div class="split-hor centered">
                  <div>
                    <div class="tour-img" id="img-reorganize-controls"></div>
                  </div>
                  <div>
                    ${reorganizeItems[2]}
                  </div>
                </div>

                ${nextBtn('intro', _('Back'))}
              </div>


              <div id="tour-slide-bugs" class="slide">
                <div class="bubble">${bugBubble}</div>

                <ul>
                  <li>${bugItems[0]}</li>
                  <li>${bugItems[1]}</li>
                  <li>${bugItems[2]}</li>
                  <li>${bugItems[3]}</li>
                </ul>

                <a href="${bugUrl}" class="action-button bgabutton bgabutton_red">${bugReport}</a>

                ${nextBtn('intro', _('Back'))}
              </div>

            </div>
            <div id="tour-slide-footer">
              <input type="checkbox" id="neverShowMe" />
              ${neverShowMe}
            </div>
          `;
      },

      /***********************************
       ********* COMBO CHECKER ************
       ***********************************/
      onEnteringStateCheckCombos(args) {
        dojo.style('position-wrapper', 'display', 'none');
        if (!$('checkCombos')) {
          dojo.place("<div id='checkCombos'></div>", 'game_play_area');
        } else {
          dojo.empty('checkCombos');
        }

        let constructTable = (data, container) => {
          let firstRow = '<tr><th></th>';
          data.cards.forEach((card) => (firstRow += '<th>' + card.numbering + '</th>'));
          firstRow += '<tr>';
          dojo.place(firstRow, container);

          data.cards.forEach((card) => {
            let row = '<tr>';
            row += '<th>' + card.numbering + ' - ' + card.name + '</th>';
            data.cards.forEach((card2) => {
              let cId = card.id;
              let cId2 = card2.id;
              if (data.order[cId] && data.order[cId][cId2]) {
                row += '<td>' + data.order[cId][cId2] + '</td>';
              } else if (cId == cId2) {
                row += '<td>X</td>';
              } else {
                row += '<td></td>';
              }
            });
            row += '</tr>';
            dojo.place(row, container);
          });
        };

        dojo.place(
          '<div class="player-board checkCombos"><h1>Construct</h1><div id="checkCombosConstruct"></div><table id="tableCombosConstruct"></table></div>',
          'checkCombos',
        );
        args.construct.cards.forEach((card) => this.addCard(card, 'checkCombosConstruct'));
        constructTable(args.construct, 'tableCombosConstruct');

        dojo.place(
          '<div class="player-board checkCombos"><h1>Renovation</h1><div id="checkCombosRenovate"></div><table id="tableCombosRenovate"></table></div>',
          'checkCombos',
        );
        args.renovate.cards.forEach((card) => this.addCard(card, 'checkCombosRenovate'));
        constructTable(args.renovate, 'tableCombosRenovate');
      },
    },
  );
});
