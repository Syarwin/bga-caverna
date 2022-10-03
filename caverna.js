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
  g_gamethemeurl + 'modules/js/Buildings.js',
  g_gamethemeurl + 'modules/js/States/Sow.js',
  g_gamethemeurl + 'modules/js/States/ReorganizeAnimals.js',
  g_gamethemeurl + 'modules/js/States/SpecialEffect.js',
], function (dojo, declare, noUiSlider, sortable) {
  const COLORBLIND = 104;
  const FONT_DOMINICAN = 105;
  const PLAYER_BOARDS = 107;
  const HAND_CARDS = 108;
  const PLAYER_RESOURCES = 109;

  const RESOURCES = [
    'gold',
    'wood',
    'stone',
    'ore',
    'ruby',
    'grain',
    'vegetable',
    'food',
    'dog',
    'sheep',
    'pig',
    'cattle',
    'donkey',
  ];
  const ANIMALS = ['dog', 'sheep', 'pig', 'cattle', 'donkey'];

  return declare(
    'bgagame.caverna',
    [
      customgame.game,
      caverna.actionCards,
      caverna.players,
      caverna.playerBoard,
      caverna.meeples,
      caverna.sow,
      caverna.buildings,
      caverna.reorganize,
      caverna.specialEffect,
    ],
    {
      constructor() {
        this._activeStates = [
          'blacksmith',
          'expedition',
          'placeDwarf',
          'resolveChoice',
          'confirmTurn',
          'confirmPartialTurn',
          'placeTile',
          'furnish',
          'breed',
          'exchange',
          'payResources',
          'reorganize',
          'sow',
          'stables',
          'harvestChoice',
        ];
        this._notifications = [
          ['startNewRound', 1],
          ['updateScores', 1],
          ['clearTurn', 700],
          ['refreshUI', 1],
          ['revealActionCard', 1100],
          ['flipWishChildren', 1100],
          ['collectResources', null],
          ['gainResources', null],
          ['payResources', null],
          ['accumulation', null],
          ['placeDwarf', null],
          ['equipWeapon', null],
          ['upgradeWeapon', 500],
          ['furnish', null],
          ['placeTile', 500],
          ['revealHarvestToken', 500],
          ['firstPlayer', 800],
          ['sow', null],
          ['returnHome', null],
          ['placeMeeplesForFuture', null],
          ['addStables', null],
          ['growFamily', null],
          ['growChildren', 1000],
          ['updateDropZones', 1],
          ['reorganize', null],
          ['harvestCrop', null],
          ['exchange', null],
          ['silentKill', null],
          ['silentDestroy', 1],
          ['startHarvest', 3200],
          ['endHarvest', null],
          ['updateHarvestCosts', 1],
          ['updateDwellingCapacity', 1],
          ['seed', 10],
        ];

        // Fix mobile viewport (remove CSS zoom)
        this.default_viewport = 'width=1000';

        this._canReorganize = false;
        this._buildingStorage = {};

        this._floatingContainerOpen = null;
        this._modalContainerOpen = null;

        this._settingsConfig = {
          confirmMode: { type: 'pref', prefId: 103 },
          autoPay: { type: 'pref', prefId: 102 },
          textFont: { type: 'pref', prefId: 105, attribute: 'textFont' },

          background: {
            default: 1,
            name: _('Background'),
            attribute: 'background',
            type: 'select',
            values: {
              0: _('Dark texture'),
              1: _('Light texture'),
              2: _('Default BGA'),
            },
          },
          dwarfAsset: {
            default: 1,
            name: _('Dwarfs asset'),
            attribute: 'dwarf',
            type: 'select',
            values: {
              0: _('Original discs'),
              1: _('Meeples'),
              2: _('Colorblind meeples'),
            },
          },
          actionBoardName: {
            default: 0,
            name: _('Action Card Names'),
            attribute: 'action-name',
            type: 'select',
            values: {
              0: _('Hidden'),
              1: _('Displayed'),
            },
          },
          actionBoardColumns: {
            default: 5,
            name: _('Action board columns'),
            type: 'slider',
            sliderConfig: {
              step: 1,
              padding: 0,
              range: {
                min: [1],
                max: [14],
              },
            },
          },
          actionBoardScale: {
            default: 100,
            name: _('Action board scale'),
            type: 'slider',
            sliderConfig: {
              step: 5,
              padding: 0,
              range: {
                min: [20],
                max: [170],
              },
            },
          },
          // otherPlayerBoard: {
          //   default: 0,
          //   name: _('Other player boards'),
          //   attribute: 'player-boards',
          //   type: 'select',
          //   values: {
          //     0: _('Under the central board'),
          //     1: _('Next to the central board'),
          //   },
          // },
          playerBoardScale: {
            default: 100,
            name: _('Player board scale'),
            type: 'slider',
            sliderConfig: {
              step: 5,
              padding: 0,
              range: {
                min: [20],
                max: [170],
              },
            },
          },
          resourceBarLocation: {
            default: 0,
            name: _('Resources bar location'),
            attribute: 'resource-bar',
            type: 'select',
            values: {
              0: _('In the top right pannel'),
              1: _('Next to the player board'),
            },
          },
        };
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
          'after'
        );
        // 3D ribbon
        dojo.place('<div id="page-title-left-ribbon" class="ribbon-effect"></div>', 'page-title');
        dojo.place('<div id="page-title-right-ribbon" class="ribbon-effect"></div>', 'page-title');
        // Create a new div for subtitle (placeTile action)
        dojo.place("<div id='page-subtitle'></div>", 'page-title', 'after');
        // Overlay for harvest animation
        dojo.place("<div id='harvest-overlay'></div>", 'ebd-body');

        dojo.attr('game_play_area', 'data-turn', gamedatas.round);
        this.setupInfoPanel();
        this.setupScoresModal();
        this.setupExpeditionModal();
        this.setupRubyModal();
        this.setupActionCards();
        this.setupPlayers();
        this.setupTiles();
        this.setupBuildings();
        this.setupMeeples();
        this.setupAnimalsDropZones();
        this.setupTour();
        // if (gamedatas.seed != false) this.showSeed(gamedatas.seed);

        this.inherited(arguments);

        // Create round counter
        this._roundCounter = this.createCounter('round-counter');
        this.updateRoundCounter();
      },

      onLoadingComplete() {
        if (localStorage.getItem('cavernaTour') != 1) {
          if (!this.isReadOnly()) this.showTour();
        } else {
          dojo.style('tour-slide-footer', 'display', 'none');
          $('neverShowMe').checked = true;
        }

        this.inherited(arguments);
      },

      onScreenWidthChange() {
      },

      updateRoundCounter() {
        let val = parseInt(this.gamedatas.round);
        this._roundCounter.toValue(val);
        $('ebd-body').dataset.round = val;
      },

      notif_startNewRound(n) {
        debug('Notif: starting new round', n);
        this.gamedatas.round = n.args.round;
        this.updateRoundCounter();
      },

      notif_clearTurn(n) {
        debug('Notif: restarting turn', n);
        this.cancelLogs(n.args.notifIds);
      },

      notif_refreshUI(n) {
        debug('Notif: refreshing UI', n);
        ['meeples', 'tiles', 'players', 'scores', 'buildings'].forEach((value) => {
          this.gamedatas[value] = n.args.datas[value];
        });
        this.setupMeeples();
        this.setupTiles();
        this.setupAnimalsDropZones();
        this.updateBuildings();
        this.updatePlayersCounters();
        this.updatePlayersScores();
      },

      notif_startHarvest(n) {
        debug('Notif: starting harvest', n);
        let elem = $('meeple-' + n.args.token.id);
        let startingPos = elem.parentNode;
        dojo.empty('harvest-overlay');
        if (this.isFastMode()) {
          dojo.place(elem, 'current-harvest');
          return;
        }

        dojo.place(elem, 'harvest-overlay');

        // Start animation
        this.slide(elem, 'harvest-overlay', {
          from: startingPos,
        });
        dojo.addClass('harvest-overlay', 'active');
        dojo.style(elem, 'transform', `scale(5)`);

        setTimeout(() => dojo.removeClass('harvest-overlay', 'active'), 1800);
        setTimeout(() => {
          this.slide(elem, 'current-harvest');
          dojo.style(elem, 'transform', `scale(1)`);
        }, 1500);
      },

      notif_endHarvest(n) {
        debug('Notif: ending harvest', n);
        let token = n.args.token;
        if (token == null) {
          dojo.empty('current-harvest');
          this.notifqueue.setSynchronousDuration(10);
        } else {
          this.slide(
            `meeple-${token.id}`,
            $('player_config').querySelector(`.harvest-indicator[data-type="${token.type}"]`)
          ).then(() => this.notifqueue.setSynchronousDuration(10));
        }
        return null;
      },

      clearPossible() {
        this.clearSowRadios();
        this.disableAnimalControls();
        dojo.empty('anytimeActions');
        dojo.empty('page-subtitle');
        dojo.query('.square-selector').forEach(dojo.destroy);
        dojo.query('.player-board-wrapper.current.harvest').removeClass('harvest');
        dojo.query('.phantom').removeClass('.phantom');
        this._isHarvest = false;
        this._selectableBuildings = [];
        this._onSelectBuildingCallback = null;
        if (this._exchangeDialog) this._exchangeDialog.hide();
        if (this._showSeedDialog) this._showSeedDialog.destroy();
        $('popin_showRuby').classList.remove('active');
        dojo.query('#buildings-container .caverna-building').removeClass('selectable selected');
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
                'anytimeActions'
              );
            });
          }
        }

        // Use ruby button
        if (args.args && args.args.canUseRuby && this.checkPossibleActions('actUseRuby')) {
          this.addPrimaryActionButton(
            'btnShowRuby',
            this.formatStringMeeples(_('Use <RUBY>')),
            () => this._rubyDialog.show(),
            'anytimeActions'
          );
          $('popin_showRuby').classList.add('active');
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
          disabled ? () => {} : () => this.takeAction('actChooseAction', { id: choice.id })
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
          true
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
      onEnteringStatePlaceDwarf(args) {
        this.promptActionCard(args.cards, (cId) => this.takeAtomicAction('actPlaceDwarf', [cId]), args.allCards);

        let weapons = Object.keys(args.possibleWeapons);
        if (args.canUseRuby && weapons.length > 1) {
          this.addPrimaryActionButton(
            'btnChangeDwarf',
            this.formatStringMeeples(_('Pay 1 <RUBY> to change dwarf')),
            () =>
              this.clientState('placeDwarfChange', _('Choose the weapon strength of the dwarf you want to place'), args)
          );
        }
      },
      onEnteringStatePlaceDwarfChange(args) {
        this.addCancelStateBtn();

        let weapons = Object.keys(args.possibleWeapons);
        weapons.forEach((weaponForce) => {
          if (weaponForce != args.weapon) {
            this.addPrimaryActionButton('btnChangeDwarf' + weaponForce, weaponForce, () => {
              this.takeAtomicAction('actPlaceDwarf', [0, args.possibleWeapons[weaponForce].id]);
            });
          }
        });
      },

      onEnteringStateImitate(args) {
        this.promptActionCard(args.cards, (cId) => this.takeAtomicAction('actImitate', [cId]), args.allCards);
      },

      onEnteringStateBlacksmith(args) {
        for (let i = 1; i <= args.max; i++) {
          let force = i;
          this.addPrimaryActionButton(
            `btnForge${force}`,
            this.format_string_recursive(_('Strength ${force}'), { force }),
            () => this.takeAtomicAction('actBlacksmith', [force])
          );
        }
      },

      onEnteringStateFurnish(args) {
        // TODO : add grey filter on other buildings
        this.promptBuilding(Object.keys(args.buildings), (buildingId) => {
          this.clientState('furnishSelectZone', _('Click on your player board to place the building'), {
            buildingId,
            zones: args.buildings[buildingId],
          });
        });
      },

      onEnteringStateFurnishSelectZone(args) {
        this.addCancelStateBtn(_('Cancel building choice'));
        $(`building-${args.buildingId}`).classList.add('selected');
        this.promptPlayerBoardZones(
          args.zones,
          1,
          1,
          (zones) => {
            this.takeAtomicAction('actFurnish', [args.buildingId, zones[0]]);
          },
          null,
          _('Confirm your building location on your board')
        );
      },

      onEnteringStateHarvestChoice(args) {
        this.addPrimaryActionButton('btnHarvestReap', _('Reap the fields'), () => {
          this.takeAtomicAction('actHarvestChoice', ['REAP']);
        });
        this.addPrimaryActionButton('btnHarvestBreed', _('Breed animals'), () => {
          this.takeAtomicAction('actHarvestChoice', ['BREED']);
        });
      },

      ////////////////////////////////////////////////////////////
      //  _____                     _ _ _   _
      // | ____|_  ___ __   ___  __| (_) |_(_) ___  _ __
      // |  _| \ \/ / '_ \ / _ \/ _` | | __| |/ _ \| '_ \
      // | |___ >  <| |_) |  __/ (_| | | |_| | (_) | | | |
      // |_____/_/\_\ .__/ \___|\__,_|_|\__|_|\___/|_| |_|
      //            |_|
      ////////////////////////////////////////////////////////////
      setupExpeditionModal() {
        // Create modal
        this._expeditionDialog = new customgame.modal('showExpedition', {
          class: 'caverna_popin',
          closeIcon: 'fa-times',
          title: _('Expedition loot'),
          closeAction: 'hide',
          verticalAlign: 'flex-start',
          contents: `<div id="expedition-header"></div>
          <div id="expedition-container">
            <div id="expedition-container-left"></div>
            <div id="expedition-container-right"></div>
          </div>
          <div id="expedition-footer"></div>`,
        });

        for (let i = 1; i <= 14; i++) {
          if (i == 13) continue;

          dojo.place(
            `<div class='expedition-lvl' data-lvl='${i}'>
            <div class='expedition-lvl-weapon' data-force='${i}'></div>
            <div id="expedition-lvl-${i}" class='expedition-lvl-container'></div>
          </div>`,
            `expedition-container-${i < 9 ? 'left' : 'right'}`
          );
        }

        let addLoot = (force, name, text) => {
          dojo.place(
            `<button data-name="${name}" class='action-button bgabutton bgabutton_gray'>${this.formatStringMeeples(
              text
            )}</button>`,
            `expedition-lvl-${force}`
          );
        };

        addLoot(1, 'increaseStrength', _('Increase all weapon strength'));
        addLoot(1, 'dog', '<DOG>');
        addLoot(1, 'wood', '<WOOD>');
        addLoot(2, 'sheep', '<SHEEP>');
        addLoot(2, 'grain', '<GRAIN>');
        addLoot(3, 'donkey', '<DONKEY>');
        addLoot(3, 'stone', '<STONE>');
        addLoot(4, 'vegetable', '<VEGETABLE>');
        addLoot(4, 'ore', '2<ORE>');
        addLoot(5, 'pig', '<PIG>');
        addLoot(6, 'gold', '2 <GOLD>');
        addLoot(7, 'furnish', '<FURNISH>');
        addLoot(8, 'stable', '<BARN>');
        addLoot(9, 'tunnel', _('<TUNNEL>'));
        addLoot(9, 'smallPasture', _('1<WOOD> for <SMALL_PASTURE>'));
        addLoot(10, 'cattle', '<CATTLE>');
        addLoot(10, 'largePasture', _('2<WOOD> for <LARGE_PASTURE>'));
        addLoot(11, 'meadow', _('Place <MEADOW>'));
        addLoot(11, 'dwelling', _('Furnish dwelling <br/> for 2 <WOOD> 2 <STONE>'));
        addLoot(12, 'field', _('Place <FIELD>'));
        addLoot(12, 'sow', _('<SOW>'));
        addLoot(14, 'cavern', _('Place <CAVERN>'));
        addLoot(14, 'breed', _('Breed up to two types of animals'));
      },

      onEnteringStateExpedition(args) {
        if (args.max == 0) return;

        this._expeditionDialog.show();
        this.addPrimaryActionButton('btnShowLoot', _('Show possible loot items'), () => this._expeditionDialog.show());
        $('popin_showExpedition').classList.add('action');
        $('expedition-header').innerHTML = $('pagemaintitletext').innerHTML;
        dojo.query('#expedition-container button').addClass('disabled');

        let selected = [];
        let selectedButtons = [];
        let selectLoot = (button) => {
          let name = button.dataset.name;
          if (selected.includes(name) || selected.length == args.n) return;
          selected.push(name);
          selectedButtons.push(button);
          button.classList.add('disabled');
          button.dataset.choiceOrder = selected.length;

          this.addSecondaryActionButton(
            'btnClearLoot',
            _('Clear'),
            () => {
              selectedButtons.forEach((button) => {
                delete button.dataset.choiceOrder;
                button.classList.remove('disabled');
              });
              selected = [];
              selectedButtons = [];
              dojo.destroy('btnClearLoot');
              dojo.destroy('btnConfirmLoot');
            },
            'expedition-footer'
          );

          dojo.destroy('btnConfirmLoot');
          let type = 'addPrimaryActionButton',
            message = _('Confirm');
          if (selected.length < args.n) {
            type = 'addDangerActionButton';
            message = this.substranslate(_('Confirm and take only ${n} loot items'), { n: selected.length });
          }
          this[type](
            'btnConfirmLoot',
            message,
            () => {
              this.takeAtomicAction('actExpedition', [selected]);
              this._expeditionDialog.hide();
            },
            'expedition-footer'
          );
        };

        for (let force = 1; force <= args.max; force++) {
          if (force == 13) continue;

          [...$(`expedition-lvl-${force}`).querySelectorAll('button')].forEach((button) => {
            button.classList.remove('disabled');
            this.onClick(button, () => selectLoot(button));
          });
        }
      },

      onLeavingStateExpedition() {
        $('popin_showExpedition').classList.remove('action');
        $('expedition-header').innerHTML = '';
        $('expedition-footer').innerHTML = '';
        [...$('expedition-container').querySelectorAll('button')].forEach((button) => {
          button.classList.remove('disabled');
          delete button.dataset.choiceOrder;
        });
      },

      onEnteringStateBreed(args) {
        if (args.automaticAction) return;
        let selected = [];

        if (args.max == 4) {
          let res = {};
          args.breeds.forEach((type) => {
            res[type] = 1;
            selected.push(type);
          });

          let msg = this.substranslate(_('Breed ${types}'), { types: this.formatResourceArray(res) });
          this.addPrimaryActionButton('breedAllButton', msg, () => {
            this.takeAtomicAction('actBreed', [selected]);
          });

          return;
        }

        args.breeds.forEach((resource) => {
          this.addPrimaryActionButton(
            resource + '-button',
            this.formatStringMeeples('<' + resource.toUpperCase() + '>'),
            () => {
              if (selected.includes(resource) || selected.length >= args.max) return;
              selected.push(resource);
              dojo.addClass(resource + '-button', 'disabled');

              let isMax = selected.length == args.max;
              let btnType = 'add' + (isMax ? 'Primary' : 'Danger') + 'ActionButton';
              let msg = isMax
                ? _('Confirm')
                : this.format_string_recursive(_('Confirm and breed only ${n}'), {
                    n: selected.length,
                  });

              this.addSecondaryActionButton('btnClearBreed', _('Clear'), () => {
                selected = [];
                dojo.query('#customActions .action-button').removeClass('disabled');
                dojo.destroy('btnClearBreed');
                dojo.destroy('btnConfirmBreed');
              });

              dojo.destroy('btnConfirmBreed');
              this[btnType]('btnConfirmBreed', msg, () => {
                this.takeAtomicAction('actBreed', [selected]);
              });
            }
          );
        });
      },

      ///////////////////////////////
      //  ____        _
      // |  _ \ _   _| |__  _   _
      // | |_) | | | | '_ \| | | |
      // |  _ <| |_| | |_) | |_| |
      // |_| \_\\__,_|_.__/ \__, |
      //                    |___/
      ///////////////////////////////

      setupRubyModal() {
        // Create modal
        this._rubyDialog = new customgame.modal('showRuby', {
          class: 'caverna_popin',
          closeIcon: 'fa-times',
          title: _('Ruby usage'),
          closeAction: 'hide',
          verticalAlign: 'flex-start',
          contents: this.formatStringMeeples(`<div id="ruby-container">
            <div id="ruby-1"><div class='ruby-container-header'>1 <RUBY></div></div>
            <div id="ruby-1-plus"><div class='ruby-container-header'>1 <RUBY> + 1 <FOOD></div></div>
            <div id="ruby-2"><div class='ruby-container-header'>2 <RUBY></div></div>
          </div>`),
        });

        let addUsage = (cost, name, text) => {
          let button = dojo.place(
            `<button data-name="${name}" class='action-button bgabutton bgabutton_gray'>${this.formatStringMeeples(
              text
            )}</button>`,
            `ruby-${cost}`
          );
          this.onClick(
            button,
            () => {
              if ($('popin_showRuby').classList.contains('active')) {
                this._rubyDialog.hide();
                this.takeAction('actUseRuby', { power: name });
              }
            },
            false
          );
        };

        addUsage(1, 'wood', '<WOOD>');
        addUsage(1, 'stone', '<STONE>');
        addUsage(1, 'ore', '<ORE>');
        addUsage(1, 'grain', '<GRAIN>');
        addUsage(1, 'vegetable', '<VEGETABLE>');

        addUsage(1, 'sheep', '<SHEEP>');
        addUsage(1, 'pig', '<PIG>');
        addUsage(1, 'dog', '<DOG>');
        addUsage(1, 'donkey', '<DONKEY>');

        addUsage(1, 'tileMeadow', '<MEADOW>');
        addUsage(1, 'tileField', '<FIELD>');
        addUsage(1, 'tileTunnel', '<TUNNEL>');

        addUsage('1-plus', 'cattle', '<CATTLE>');

        addUsage(2, 'tileCavern', '<CAVERN>');
      },

      //////////////////////////////////////////////////////////
      //  _____          _
      // | ____|_  _____| |__   __ _ _ __   __ _  ___  ___
      // |  _| \ \/ / __| '_ \ / _` | '_ \ / _` |/ _ \/ __|
      // | |___ >  < (__| | | | (_| | | | | (_| |  __/\__ \
      // |_____/_/\_\___|_| |_|\__,_|_| |_|\__, |\___||___/
      //                                   |___/
      //////////////////////////////////////////////////////////

      /**
       * Entering the exchange state : create modal and init selection
       */
      onEnteringStateExchange(args) {
        this._possibleExchanges = args.exchanges;
        this._originalReserve = args.resources;
        this._currentExchanges = []; // Stack of exchanges we want to make
        this._exchangeTrigger = args.trigger;
        this._extraAnimals = args.extraAnimals;
        this._mandatoryExchange = args.mandatory;

        if (!args.automaticAction) {
          // Setup and display dialog
          this.addPrimaryActionButton('btnShowPossibleExchanges', _('Show possible exchanges'), () =>
            this._exchangeDialog.show()
          );
          this.setupExchangeModal();
        }
      },

      /**
       * Create exchange modal along with counters and buttons
       */
      setupExchangeModal() {
        if (this._exchangeDialog) {
          this._exchangeDialog.kill();
        }

        // Create modal
        this._exchangeDialog = new customgame.modal('showExchanges', {
          autoShow: true,
          class: 'caverna_popin',
          closeIcon: 'fa-times',
          title: _('Exchange center'),
          closeAction: 'hide',
          verticalAlign: 'flex-start',
          contents: `
            <div id="exchanges-container">
              <div id="exchanges-grid"></div>
            </div>
            <div id="exchanges-reserve"></div>
            <div id="exchanges-dialog-footer"></div>
          `,
        });

        // Setup counters
        this._exchangesCounters = {};
        RESOURCES.forEach((res) => {
          dojo.place(this.tplResourceCounter({ id: this.player_id }, res, 'exchange_'), 'exchanges-reserve');
          this._exchangesCounters[res] = new ebg.counter();
          this._exchangesCounters[res].create('exchange_resource_' + this.player_id + '_' + res);
        });

        // Create rows
        this._possibleExchanges.forEach((exchange, i) => {
          exchange.id = i;
          this.place('tplExchangeRow', exchange, 'exchanges-grid');
          this.connect('exchange-' + i, 'click', () => this.onClickExchange(i));
        });

        // Has extra animals ?
        if (
          this._extraAnimals.sheep + this._extraAnimals.pig + this._extraAnimals.cattle + this._extraAnimals.donkey !=
          0
        ) {
          dojo.place(
            '<h3>' + _('Excess animals in reserve:') + ' ' + this.computeExtraAnimalsDesc() + '</h3>',
            'exchanges-container',
            'before'
          );
        }

        this.updateExchangeCounters();
      },

      /**
       * Given original reserve and list of exchanges, compute the new reserve
       */
      computeReserveAfterExchange() {
        // Deep copy of originalReserve
        let newReserve = {};
        RESOURCES.forEach((res) => (newReserve[res] = this._originalReserve[res]));

        // Apply exchanges
        this._currentExchanges.forEach((ex) => {
          this.addResourcesArrays(newReserve, this._possibleExchanges[ex].from, -1);
          this.addResourcesArrays(newReserve, this._possibleExchanges[ex].to, 1);
        });

        return newReserve;
      },

      /**
       * Compute extra animals
       */
      computeExtraAnimalsDesc() {
        let newReserve = this.computeReserveAfterExchange();
        let extraAnimals = [];
        ANIMALS.forEach((type) => {
          let n = this._extraAnimals[type] - (this._originalReserve[type] - newReserve[type]);
          if (n > 0) {
            extraAnimals.push(this.formatStringMeeples(n + '<' + type.toUpperCase() + '>'));
          }
        });

        return extraAnimals.join(',');
      },

      /**
       * Check if a given exchange (given by index) can be used given new reserve
       */
      canUseExchange(ex) {
        let newReserve = this.computeReserveAfterExchange();
        let exchange = this._possibleExchanges[ex];
        return (
          // Enough resources
          RESOURCES.reduce(
            (acc, res) => acc && newReserve[res] >= (exchange.from[res] == undefined ? 0 : exchange.from[res]),
            true
          ) &&
          // If max is supplied, shouldn't be reached already
          (exchange.max == undefined || exchange.max > this._currentExchanges.filter((e) => e == ex).length)
        );
      },

      /**
       * Called when clicking on an exchange
       */
      onClickExchange(ex) {
        let exchange = this._possibleExchanges[ex];
        if (!this.canUseExchange(ex)) return;

        this._currentExchanges.push(ex);
        this.updateExchangeCounters();
      },

      /**
       * Reset all current exchanges
       */
      clearExchanges() {
        this._currentExchanges = [];
        this.updateExchangeCounters();
      },

      /**
       * Exchange notification
       */
      notif_exchange(n) {
        debug('Notif: exchanging resources');

        // Close modal if needed
        let initDelay = 0;
        if (this._exchangeDialog) {
          this._exchangeDialog.hide();
          initDelay = 500;
        }

        // Flag deleted meeple
        let deleted = n.args.resources;
        deleted.forEach((meeple, i) => {
          meeple.animDelay = initDelay + i * 100;
          meeple.animDestroy = true;
        });
        // Flag created meeple and add DELAY
        let delay = initDelay + 800 + deleted.length * 100 + 500;
        let created = n.args.resources2;
        created.forEach((meeple, i) => {
          meeple.animDelay = delay + i * 100;
          meeple.animDestroy = false;
        });

        // Run animation
        this.slideResources(deleted.concat(created), (meeple) => ({
          delay: meeple.animDelay,
          from: meeple.animDestroy ? null : 'page-title',
          target: meeple.animDestroy ? 'page-title' : null,
          destroy: meeple.animDestroy,
        }));
      },

      /**
       * Update exchange counters and buttons
       */
      updateExchangeCounters() {
        // Update counters
        let newReserve = this.computeReserveAfterExchange();
        RESOURCES.forEach((res) => {
          this._exchangesCounters[res].toValue(newReserve[res]);
        });

        // Update disabled state of buttons
        this._possibleExchanges.forEach((exchange, i) => {
          $('exchange-' + i).disabled = !this.canUseExchange(i);
        });

        // Update clear/confirm buttons
        dojo.destroy('btnClearExchange');
        if (this._currentExchanges.length > 0) {
          this.addSecondaryActionButton(
            'btnClearExchange',
            _('Clear'),
            () => this.clearExchanges(),
            'exchanges-dialog-footer'
          );
        }

        dojo.destroy('btnConfirmExchange');
        dojo.destroy('btnPassExchange');

        let confirmExchanges = () => {
          this.takeAtomicAction('actExchange', [this._currentExchanges]);
        };

        let begging = 0; //this._harvestMinFood - newReserve['food'];
        let extraAnimals = this.computeExtraAnimalsDesc();
        if (begging > 0) {
          this.addDangerActionButton(
            'btnConfirmExchange',
            dojo.string.substitute(this.formatStringMeeples(_('Confirm and take ${begging} <BEGGING>')), { begging }),
            () => confirmExchanges(),
            'exchanges-dialog-footer'
          );
        } else if (extraAnimals != '' && this._mandatoryExchange) {
          this.addDangerActionButton(
            'btnConfirmExchange',
            dojo.string.substitute(this.formatStringMeeples(_('Confirm and discard ${extraAnimals}')), {
              extraAnimals,
            }),
            () => confirmExchanges(),
            'exchanges-dialog-footer'
          );
        } else if (this._currentExchanges.length > 0) {
          this.addPrimaryActionButton(
            'btnConfirmExchange',
            _('Confirm'),
            () => confirmExchanges(),
            'exchanges-dialog-footer'
          );
        } else {
          if (!this._mandatoryExchange || this.gamedatas.gamestate.args.optionalAction) {
            this.addSecondaryActionButton(
              'btnPassExchange',
              _('Pass'),
              () => this.takeAction('actPassOptionalAction'),
              'exchanges-dialog-footer'
            );
          }
        }
      },

      /**
       * Add up two arrays of resources
       */
      addResourcesArrays(t1, t2, n = 1) {
        RESOURCES.forEach((res) => (t1[res] += (t2[res] ? t2[res] : 0) * n));
      },

      /**
       * Exchange row template : basically a button
       */
      tplExchangeRow(exchange) {
        let source = exchange.source ? `<div class='exchange-source'>${_(exchange.source)}</div>` : '';
        let arrowN = exchange.max && exchange.max != 9999 ? '-' + exchange.max + 'X' : '';
        let desc = this.formatStringMeeples(
          this.formatResourceArray(exchange.from, false) +
            `<ARROW${arrowN}>` +
            this.formatResourceArray(exchange.to, false)
        );

        return `<div class='exchange-item ${source != ''? 'exchange-with-source' : ''}'>
            ${source}
            <button class='exchange-desc' id="exchange-${exchange.id}">${desc}</button>
          </div>`;
      },

      onEnteringStateStables(args) {
        this.promptPlayerBoardZones(args.zones, 1, args.max, (zones) => this.takeAtomicAction('actStables', [zones]));
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

      onEnteringStateRubyChoice(args) {
        let selectedCards = [];
        args.cards.forEach((cardId) => {
          this.onClick(cardId, () => {
            if (selectedCards.includes(cardId) || selectedCards.length >= args.rubies) return;

            selectedCards.push(cardId);
            $(cardId).classList.add('selected');
            this.addSecondaryActionButton('btnClear', _('Clear'), () => {
              selectedCards = [];
              dojo.query('.selected').removeClass('selected');
              $('btnClear').remove();
              $('btnConfirm').remove();
              this.addPrimaryActionButton('btnConfirm', _("Don't use any ruby"), () =>
                this.takeAction('actRubyChoice', { cards: JSON.stringify(selectedCards) })
              );
            });

            $('btnConfirm').remove();
            this.addPrimaryActionButton(
              'btnConfirm',
              this.formatStringMeeples(
                this.format_string(_('Confirm and use ${n} <RUBY>'), { n: selectedCards.length })
              ),
              () => this.takeAction('actRubyChoice', { cards: JSON.stringify(selectedCards) })
            );
          });
        });
        this.addPrimaryActionButton('btnConfirm', _("Don't use any ruby"), () =>
          this.takeAction('actRubyChoice', { cards: JSON.stringify(selectedCards) })
        );
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
        dojo.place(this.tplConfigPlayerBoard(), 'player_boards', 'first');

        let chk = $('help-mode-chk');
        dojo.connect(chk, 'onchange', () => this.toggleHelpMode(chk.checked));
        this.addTooltip('help-mode-switch', '', _('Toggle help/safe mode.'));

        this.onClick('show-expedition', () => this._expeditionDialog.show(), false);
        this.addTooltip('show-expedition', '', _('Show expedition help sheet'));

        this.onClick('show-ruby', () => this._rubyDialog.show(), false);
        this.addTooltip('show-ruby', '', _('Show Ruby help sheet'));

        this._settingsModal = new customgame.modal('showSettings', {
          class: 'barrage_popin',
          closeIcon: 'fa-times',
          title: _('Settings'),
          closeAction: 'hide',
          verticalAlign: 'flex-start',
          contentsTpl: `<div id='barrage-settings'>
             <div id='barrage-settings-header'></div>
             <div id="settings-controls-container"></div>
           </div>`,
        });

        // Red harvest tooltips
        if ($('harvest-indicator-harvest_red')) {
          this.addCustomTooltip(
            'harvest-indicator-harvest_red',
            _(
              'This section concerns harvest events that might take place after round 4. During all the remaining rounds, a normal harvest will take place except for 3 of them.'
            ) +
              '<br/>' +
              _(
                'The first harvest event is that no harvest will take place at the end of the round where it is revealed.'
              ) +
              '<br/>' +
              _(
                'The second harvest event is similar to the partial harvest of round 4: no harvest but you have to pay 1 Food per Dwarf in yout cave.'
              ) +
              '<br/>' +
              _(
                'During the third harvest event, each player will decide individually whether they want to play the Field phase or the Breeding phase of the Harvest time at the end of the round. (You cannot play both these phases, but you must still play the Feeding phase. '
              )
          );
        }
      },

      tplConfigPlayerBoard() {
        let nPlayers = Object.keys(this.gamedatas.players).length;

        return (
          `
   <div class='player-board' id="player_board_config">
     <div id="player_config" class="player_board_content">

       <div class="player_config_row" id="round-counter-wrapper">
         ${_('Round')} <span id='round-counter'></span> / ${nPlayers <= 2 ? 11 : 12}
         <div id="current-harvest"></div>
       </div>
       <div class="player_config_row">
         <div id="uwe-help"></div>

         <div id="help-mode-switch">
           <input type="checkbox" class="checkbox" id="help-mode-chk" />
           <label class="label" for="help-mode-chk">
             <div class="ball"></div>
           </label>

           <svg aria-hidden="true" focusable="false" data-prefix="fad" data-icon="question-circle" class="svg-inline--fa fa-question-circle fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><g class="fa-group"><path class="fa-secondary" fill="currentColor" d="M256 8C119 8 8 119.08 8 256s111 248 248 248 248-111 248-248S393 8 256 8zm0 422a46 46 0 1 1 46-46 46.05 46.05 0 0 1-46 46zm40-131.33V300a12 12 0 0 1-12 12h-56a12 12 0 0 1-12-12v-4c0-41.06 31.13-57.47 54.65-70.66 20.17-11.31 32.54-19 32.54-34 0-19.82-25.27-33-45.7-33-27.19 0-39.44 13.14-57.3 35.79a12 12 0 0 1-16.67 2.13L148.82 170a12 12 0 0 1-2.71-16.26C173.4 113 208.16 90 262.66 90c56.34 0 116.53 44 116.53 102 0 77-83.19 78.21-83.19 106.67z" opacity="0.4"></path><path class="fa-primary" fill="currentColor" d="M256 338a46 46 0 1 0 46 46 46 46 0 0 0-46-46zm6.66-248c-54.5 0-89.26 23-116.55 63.76a12 12 0 0 0 2.71 16.24l34.7 26.31a12 12 0 0 0 16.67-2.13c17.86-22.65 30.11-35.79 57.3-35.79 20.43 0 45.7 13.14 45.7 33 0 15-12.37 22.66-32.54 34C247.13 238.53 216 254.94 216 296v4a12 12 0 0 0 12 12h56a12 12 0 0 0 12-12v-1.33c0-28.46 83.19-29.67 83.19-106.67 0-58-60.19-102-116.53-102z"></path></g></svg>
         </div>

         <div id="show-settings">
           <svg  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512">
             <g>
               <path class="fa-secondary" fill="currentColor" d="M638.41 387a12.34 12.34 0 0 0-12.2-10.3h-16.5a86.33 86.33 0 0 0-15.9-27.4L602 335a12.42 12.42 0 0 0-2.8-15.7 110.5 110.5 0 0 0-32.1-18.6 12.36 12.36 0 0 0-15.1 5.4l-8.2 14.3a88.86 88.86 0 0 0-31.7 0l-8.2-14.3a12.36 12.36 0 0 0-15.1-5.4 111.83 111.83 0 0 0-32.1 18.6 12.3 12.3 0 0 0-2.8 15.7l8.2 14.3a86.33 86.33 0 0 0-15.9 27.4h-16.5a12.43 12.43 0 0 0-12.2 10.4 112.66 112.66 0 0 0 0 37.1 12.34 12.34 0 0 0 12.2 10.3h16.5a86.33 86.33 0 0 0 15.9 27.4l-8.2 14.3a12.42 12.42 0 0 0 2.8 15.7 110.5 110.5 0 0 0 32.1 18.6 12.36 12.36 0 0 0 15.1-5.4l8.2-14.3a88.86 88.86 0 0 0 31.7 0l8.2 14.3a12.36 12.36 0 0 0 15.1 5.4 111.83 111.83 0 0 0 32.1-18.6 12.3 12.3 0 0 0 2.8-15.7l-8.2-14.3a86.33 86.33 0 0 0 15.9-27.4h16.5a12.43 12.43 0 0 0 12.2-10.4 112.66 112.66 0 0 0 .01-37.1zm-136.8 44.9c-29.6-38.5 14.3-82.4 52.8-52.8 29.59 38.49-14.3 82.39-52.8 52.79zm136.8-343.8a12.34 12.34 0 0 0-12.2-10.3h-16.5a86.33 86.33 0 0 0-15.9-27.4l8.2-14.3a12.42 12.42 0 0 0-2.8-15.7 110.5 110.5 0 0 0-32.1-18.6A12.36 12.36 0 0 0 552 7.19l-8.2 14.3a88.86 88.86 0 0 0-31.7 0l-8.2-14.3a12.36 12.36 0 0 0-15.1-5.4 111.83 111.83 0 0 0-32.1 18.6 12.3 12.3 0 0 0-2.8 15.7l8.2 14.3a86.33 86.33 0 0 0-15.9 27.4h-16.5a12.43 12.43 0 0 0-12.2 10.4 112.66 112.66 0 0 0 0 37.1 12.34 12.34 0 0 0 12.2 10.3h16.5a86.33 86.33 0 0 0 15.9 27.4l-8.2 14.3a12.42 12.42 0 0 0 2.8 15.7 110.5 110.5 0 0 0 32.1 18.6 12.36 12.36 0 0 0 15.1-5.4l8.2-14.3a88.86 88.86 0 0 0 31.7 0l8.2 14.3a12.36 12.36 0 0 0 15.1 5.4 111.83 111.83 0 0 0 32.1-18.6 12.3 12.3 0 0 0 2.8-15.7l-8.2-14.3a86.33 86.33 0 0 0 15.9-27.4h16.5a12.43 12.43 0 0 0 12.2-10.4 112.66 112.66 0 0 0 .01-37.1zm-136.8 45c-29.6-38.5 14.3-82.5 52.8-52.8 29.59 38.49-14.3 82.39-52.8 52.79z" opacity="0.4"></path>
               <path class="fa-primary" fill="currentColor" d="M420 303.79L386.31 287a173.78 173.78 0 0 0 0-63.5l33.7-16.8c10.1-5.9 14-18.2 10-29.1-8.9-24.2-25.9-46.4-42.1-65.8a23.93 23.93 0 0 0-30.3-5.3l-29.1 16.8a173.66 173.66 0 0 0-54.9-31.7V58a24 24 0 0 0-20-23.6 228.06 228.06 0 0 0-76 .1A23.82 23.82 0 0 0 158 58v33.7a171.78 171.78 0 0 0-54.9 31.7L74 106.59a23.91 23.91 0 0 0-30.3 5.3c-16.2 19.4-33.3 41.6-42.2 65.8a23.84 23.84 0 0 0 10.5 29l33.3 16.9a173.24 173.24 0 0 0 0 63.4L12 303.79a24.13 24.13 0 0 0-10.5 29.1c8.9 24.1 26 46.3 42.2 65.7a23.93 23.93 0 0 0 30.3 5.3l29.1-16.7a173.66 173.66 0 0 0 54.9 31.7v33.6a24 24 0 0 0 20 23.6 224.88 224.88 0 0 0 75.9 0 23.93 23.93 0 0 0 19.7-23.6v-33.6a171.78 171.78 0 0 0 54.9-31.7l29.1 16.8a23.91 23.91 0 0 0 30.3-5.3c16.2-19.4 33.7-41.6 42.6-65.8a24 24 0 0 0-10.5-29.1zm-151.3 4.3c-77 59.2-164.9-28.7-105.7-105.7 77-59.2 164.91 28.7 105.71 105.7z"></path>
             </g>
           </svg>
         </div>
       </div>
       <div class="player_config_row">
         <div id="show-scores">
           <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
             <g class="fa-group">
               <path class="fa-secondary" fill="currentColor" d="M0 192v272a48 48 0 0 0 48 48h352a48 48 0 0 0 48-48V192zm324.13 141.91a11.92 11.92 0 0 1-3.53 6.89L281 379.4l9.4 54.6a12 12 0 0 1-17.4 12.6l-49-25.8-48.9 25.8a12 12 0 0 1-17.4-12.6l9.4-54.6-39.6-38.6a12 12 0 0 1 6.6-20.5l54.7-8 24.5-49.6a12 12 0 0 1 21.5 0l24.5 49.6 54.7 8a12 12 0 0 1 10.13 13.61zM304 128h32a16 16 0 0 0 16-16V16a16 16 0 0 0-16-16h-32a16 16 0 0 0-16 16v96a16 16 0 0 0 16 16zm-192 0h32a16 16 0 0 0 16-16V16a16 16 0 0 0-16-16h-32a16 16 0 0 0-16 16v96a16 16 0 0 0 16 16z" opacity="0.4"></path>
               <path class="fa-primary" fill="currentColor" d="M314 320.3l-54.7-8-24.5-49.6a12 12 0 0 0-21.5 0l-24.5 49.6-54.7 8a12 12 0 0 0-6.6 20.5l39.6 38.6-9.4 54.6a12 12 0 0 0 17.4 12.6l48.9-25.8 49 25.8a12 12 0 0 0 17.4-12.6l-9.4-54.6 39.6-38.6a12 12 0 0 0-6.6-20.5zM400 64h-48v48a16 16 0 0 1-16 16h-32a16 16 0 0 1-16-16V64H160v48a16 16 0 0 1-16 16h-32a16 16 0 0 1-16-16V64H48a48 48 0 0 0-48 48v80h448v-80a48 48 0 0 0-48-48z"></path>
             </g>
           </svg>
         </div>

         <div id="show-ruby">
         <svg  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 22.535551 17.212789">
           <path
              style="opacity:0.65899999;fill:#e12d2d;fill-opacity:1;stroke:#000000;stroke-width:0.62362206;stroke-linecap:butt;stroke-linejoin:bevel;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
              d="M 0.31018875,7.8099019 C 0.53196435,6.8223101 3.2526464,6.1207132 4.7238752,5.2761189 l 4.352385,-4.96539734 c 1.0338148,0.0866484 1.2104578,-0.21874313 9.0112768,2.96289134 1.994977,1.5582693 2.083729,2.7352934 2.840289,4.0458793 0.347353,0.4358572 0.639348,0.7056428 1.04212,1.307759 0.0879,0.5158494 0.358321,0.00953 0.204338,1.8799038 -2.580966,1.633346 -2.471432,1.610999 -3.698506,2.41118 -2.067356,2.189707 -3.386894,2.767213 -5.108434,3.330699 -1.49019,-0.01399 -3.014473,-0.01946 -4.7201918,0.02043 -0.5148785,0.133449 -1.0185087,0.25565 -1.7981686,0.65388 C 4.8061816,16.104929 4.0291283,14.811858 2.6192007,13.756115 -0.37668715,9.8830288 0.50109345,9.6740684 0.31018875,7.8099019 Z" />
           <path
              style="fill:none;stroke:#ffffff;stroke-width:0.4;stroke-linecap:butt;stroke-linejoin:bevel;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
              d="m 9.73014,1.3528419 c 0.512886,0.9912361 2.916032,1.9988155 4.679325,5.0471323 -1.582309,1.9292326 -1.842728,2.8198375 -2.73812,4.2093498 C 10.4054,11.611933 9.7484973,11.294948 8.7901883,11.631011 3.0879456,10.162175 5.0044469,9.0230166 1.822285,8.1368416" />
           <path
              style="fill:none;stroke:#fffffa;stroke-width:0.4;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
              d="m 14.409465,6.3999742 c 3.258647,0.9753939 2.364301,2.1353653 3.432867,3.2080962 -4.503394,2.2468656 -3.329952,3.2414876 -4.536289,4.7610606 -1.094702,-0.0088 -1.793867,0.140671 -3.3715656,-0.0613 C 9.4432221,13.452167 9.1232745,12.5394 8.7901883,11.631012"/>
           <path
              style="fill:none;stroke:#ffffff;stroke-width:0.4;stroke-linecap:butt;stroke-linejoin:miter;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
              d="m 17.842332,9.6080704 c 0.898877,0.1399393 2.075182,0.5176746 2.635952,0.3678072"/>
           <path
              style="fill:none;stroke:#ffffef;stroke-width:0.4;stroke-linecap:butt;stroke-linejoin:bevel;stroke-miterlimit:4;stroke-dasharray:none;stroke-opacity:1"
              d="M 9.9344774,14.307831 8.5654172,15.636022"/>
           <path
              style="fill:#000000;fill-opacity:1;stroke-width:0.01918079"
              d="m 11.577839,3.7101047 c -1.556973,0 -2.5654274,0.6168006 -3.3571682,1.7168143 -0.143625,0.1995463 -0.099346,0.4731408 0.1010029,0.620029 L 9.1631371,6.6638636 C 9.3654742,6.8122022 9.653367,6.777519 9.8117794,6.5856296 10.300393,5.993758 10.662838,5.6529858 11.426062,5.6529858 c 0.600092,0 1.342344,0.3734233 1.342344,0.9360767 0,0.425345 -0.363147,0.643791 -0.955672,0.9649903 -0.690969,0.3745728 -1.605348,0.8407363 -1.605348,2.0068765 v 0.1846065 c 0,0.2499985 0.209594,0.4526562 0.46815,0.4526562 h 1.413643 c 0.258555,0 0.468151,-0.2026577 0.468151,-0.4526562 v -0.108882 c 0,-0.8083725 2.443515,-0.84204 2.443515,-3.0295454 1.9e-5,-1.6473683 -1.767291,-2.8970041 -3.423006,-2.8970037 z m -0.195471,7.0437073 c -0.745062,0 -1.351223,0.586096 -1.351223,1.306502 0,0.720386 0.606161,1.306481 1.351223,1.306481 0.745061,0 1.351219,-0.586095 1.351219,-1.3065 0,-0.720405 -0.606158,-1.306483 -1.351219,-1.306483 z"/>
          </svg>
         </div>

         <div id="show-expedition">
         <svg  xmlns="http://www.w3.org/2000/svg" viewBox="0 0 117.67605 86.482559">
           <path
              style="opacity:0.55800003;vector-effect:none;fill:#8f8e8e;fill-opacity:0.99607843;stroke:#000000;stroke-width:4.2607007;stroke-linecap:round;stroke-linejoin:round;stroke-miterlimit:4;stroke-dasharray:none;stroke-dashoffset:0;stroke-opacity:1"
              d="M 16.039589,51.555093 A 36.075737,38.028088 0 0 1 47.081123,8.8843112 36.075737,38.028088 0 0 1 87.569595,41.59421 36.075737,38.028088 0 0 1 56.549968,84.282689 36.075737,38.028088 0 0 1 16.044712,51.595887" />
           <path
              style="fill:#000000;fill-opacity:0.99607843;stroke:#000000;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
              d="m 1.2815216,73.060732 7.316088,-7.31608 0.350471,-1.48951 1.2704594,-1.09522 2.979006,-0.0876 2.738057,-1.37998 0.459993,1.22665 1.22665,2.65044 1.226649,2.38759 1.204745,1.83997 -1.905687,0.9638 -0.898083,1.31426 -0.898083,0.91999 -0.459993,0.24095 -3.548522,-0.81047 z"/>
           <path
              style="fill:#000000;fill-opacity:0.99607843;stroke:#000000;stroke-width:1px;stroke-linecap:butt;stroke-linejoin:miter;stroke-opacity:1"
              d="m 85.655661,24.229012 0.83237,-4.07423 c 0.57923,-15.58975 -1.38702,-12.72472 -2.10283,-18.92545 l 1.4457,-0.65713004 C 98.209391,9.092222 99.256451,13.512692 101.64591,16.869112 c 1.08319,-0.0619 2.14603,0.19649 3.15425,1.31427 l 5.38849,-0.0876 0.43809,0.78856 -3.06662,4.64375 c -0.0252,0.71686 0.01,1.45387 -0.35047,2.05901 l -2.32188,1.62093 0.17524,2.01521 c 0.18071,0.83237 1.74141,1.66474 2.75996,2.49711 1.18284,-0.15055 2.36568,0.0939 3.54852,0.52571 l 4.77517,-2.49711 0.9638,0.17524 c -0.2685,3.06701 1.23415,2.64382 -2.67234,12.87982 l -1.22665,0.87617 c -0.17524,0.596 -0.35047,1.12548 -0.52571,1.88379 l -0.9638,0.96379 -1.53331,-0.13142 v 1.9714 l -2.32187,2.67234 c -4.7846,3.68451 -6.83056,4.54202 -8.017029,4.51232 l -0.91999,-0.43809 -0.78856,0.74475 c -1.25631,0.37685 -2.03007,0.65718 -3.81137,1.13903 -1.78777,0.44867 -3.86304,0.0349 -5.8704,-0.17523 4.66369,-20.40599 -1.7545,-20.9265 -2.80377,-32.59385 z" />
           <path
              style="fill:#000000;fill-opacity:1;stroke-width:0.09777062"
              d="m 52.399173,22.678706 c -8.015571,0 -13.207274,3.112972 -17.283294,8.664697 -0.739406,1.007102 -0.511448,2.387926 0.51998,3.129264 l 4.332003,3.113552 c 1.041668,0.748656 2.52379,0.573614 3.339323,-0.394842 2.515473,-2.987154 4.381401,-4.707021 8.310614,-4.707021 3.089375,0 6.910617,1.884656 6.910617,4.724346 0,2.146698 -1.86954,3.249187 -4.919961,4.87027 -3.557238,1.890453 -8.264615,4.243166 -8.264615,10.128626 v 0.931704 c 0,1.261736 1.079028,2.284543 2.410115,2.284543 h 7.277684 c 1.331084,0 2.410121,-1.022807 2.410121,-2.284543 v -0.549523 c 0,-4.079826 12.579646,-4.249741 12.579646,-15.290001 9.8e-5,-8.314206 -9.098328,-14.621072 -17.622233,-14.621072 z m -1.006327,35.549326 c -3.835704,0 -6.956323,2.958009 -6.956323,6.593869 0,3.635759 3.120619,6.593759 6.956323,6.593759 3.835712,0 6.956321,-2.958 6.956321,-6.593859 0,-3.63585 -3.120609,-6.593769 -6.956321,-6.593769 z"/>
         </svg>
         </div>
       </div>` +
          (nPlayers == 1
            ? ''
            : `<div class="player_config_row">
            <div class='harvest-indicator' id="harvest-indicator-harvest_red" data-type='harvest_red'></div>
            <div class='harvest-indicator' data-type='harvest_none'></div>
            <div class='harvest-indicator' data-type='harvest_1food'></div>
            <div class='harvest-indicator' data-type='harvest_choice'></div>
          </div>`) +
          `
     </div>
   </div>
   `
        );
      },

      updatePlayerOrdering() {
        this.inherited(arguments);
        dojo.place('player_board_config', 'player_boards', 'first');
      },

      onChangeActionBoardColumnsSetting(val) {
        this.computeCentralBoardSize();
      },

      onChangeActionBoardScaleSetting(val) {
        this.computeCentralBoardSize();
      },

      onChangePlayerBoardScaleSetting(val) {
        let elt = document.documentElement;
        elt.style.setProperty('--cavernaPlayerBoardScale', val / 100);
      },

      computeCentralBoardSize() {
        $('central-board').style.width = 183 * this.settings.actionBoardColumns + 'px';

        let elt = document.documentElement;
        let scale = this.settings.actionBoardScale;
        elt.style.setProperty('--cavernaCentralBoardScale', scale / 100);
        $('central-board-wrapper').style.width = ($('central-board').offsetWidth * scale) / 100 + 'px';
        $('central-board-wrapper').style.height = (($('central-board').offsetHeight + 30) * scale) / 100 + 'px';
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
          'seed-dialog-footer'
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
          { seed }
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
          title: _('Caverna Tour'),
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
          "Welcome to Caverna on BGA. I'm here to give you a tour of the interface, to make sure you'll enjoy your games to the fullest."
        );
        let introSectionUI = _('Global interface overview');
        let introSectionScoring = _('Scoring');
        let introSectionCards = _('Cards FAQ');
        let introSectionBugs = _('Report a bug');

        let panelInfoBubble = _("Let's start with this panel next to your name: a very handy toolbox.");
        let panelInfoItems = [
          _('round number: the last revealed action card is also highlighted in yellow on the board'),
          _('my face: open this tour if you need it later'),
          _(
            "the switch will allow to toggle the safe/help mode: when enabled, clicking will do nothing, and instead will open tooltips on any elements with a question mark on it, making it sure you won't misclick"
          ),
          _(
            "settings: this implementation comes with a lot of ways to customize your experience to your needs. Take some time to play with them until you're comfortable"
          ),
          _('the star calendar: details of live scores (only if corresponding game option was enabled)'),
          _('the ruby helper: a reminder of all the possible exchanges using ruby in Caverna'),
          _('the loot helper: an helpsheet of all the possible loots during an expedition'),
          _('special harvest tracker: keep track of the passed and future harvest events. Hover on the red token to have a reminder of the event behavior')
        ];

        let centralBoardBubble = _(
          'This is the central board where you take your actions. It consists of these parts:'
        );
        let centralBoardItems = [
          _(
            'Primary action card spaces: available at the start of the game, depends on the number of players'
          ),
          _(
            'Action space cards: one card is revealed at the start of each round. They are organized in 4 stages, randomized within each stage (the question mark on the round numbers preview the possible action space cards.) On the right of each card, an havest token is here to remind you what will happen at the end of that round: hover on the token to have some precision.'
          ),
        ];

        let cardsBtnBubble = _('Where are all the buildings?');
        let cardsBtnItems = [
          _('On the very bottom left on your screen, you will find these buttons.'),
          _('Click on any of them to open the buildings tabs:'),
          _(
            'Buildings are organized into four board, following physical components. Click on another button to navigate from one category to another, and click the X to close that tab once you are done.'
          ),
          _('You can always check a building details by hovering on it to see the tooltip, or by clicking on it to display a little modal with building details. This also work for buildings on player boards.')
        ];

        let playerBoardBubble = _(
          'This is your personal board. Here you can build tunnels, caverns, mines, meadow, pastures, stables, plow field or furnish buildings.'
        );
        let playerPanelBubble = _('These player panels contain a lot of useful information!');
        let playerPanelItems = [
          _('Top section: resources and food in personal supply.'),
          _('Middle section: animals currently on farm, and actions remaining.'),
          _(
            'Bottom section: on the right, a reminder that each player can only have up to 3 stables! On the left, a summary of current status for the player\'s dwarfs, details below.'
          ),
          _('Dwarfs on the left are placed on action board already; dwarfs in the middle are on the player\'s board, waiting to be placed, dwarfs on the right are potential children (according to player\'s current dwellings capacity) you might get if you take a "With for Children" action. Don\'t forgot that each player can only have 5 dwarfs at maximum (except by playing a special dwelling)!'),
          _(
            'The number after the / in your food reserve is a reminder of how much food the player currently needs for the next harvest, based on current public information.'
          ),
        ];

        let cookBubble = _(
          'In Caverna, cooking animals and seed can be a crucial step to provide the food you need to feed your people.'
        );
        let cookItems = [
          _(
            'All resource conversions happens in this window: just click on the exchanges you want to make before hitting "Confirm".'
          ),
          _(
            'If you need to make an exchange, just click on the left button to open the previous modal (button is only present if you can do at least one exchange).'
          ),
          _(
            'Similarly, if you have at least one ruby, the button on the right will be displayed. Click on it to open the ruby modal and click on the exchange you want to make with your ruby(ies)'
          ),
        ];

        let reorganizeBubble = _(
          'Whenever you obtain animals, you must immediately accomodate them in your farm or cook them (except for dogs that can wander around)'
        );
        let reorganizeItems = [
          _(
            'Most of the time, the game will automatically accommodate your animals in the best spot ever. However, you may also arrange them manually by either changing the "automatic" setting or by clicking this button:'
          ),
          _(
            'Click on the small arrows that pop up in your pastures, stables, buildings, meadows (if you have dogs) to choose how your animals should be arranged.'
          ),
          _(
            'These controls will clear all, decrease, increase, or maximize a specific animal type in each of your pastures/stables/buildings/meadows.'
          ),
        ];

        let bugBubble = _('No code is error-free. Before reporting a bug, please follow the following steps.');
        let bugItems = [
          _(
            'If the issue is related to a building, please use the english name.'
          ),
          _(
            'If your language is not English, please check the English building description. If there is an incorrect translation to your language, please do not report a bug and use the translation module (Community > Translation) to fix it directly.'
          ),
          _(
            'For rules-related bugs, please double-check the rulebook before reporting to make sure you have it correct.'
          ),
          _(
            'When you encounter a bug, please refresh your page to see if the bug goes away. Knowing whether or not a bug is persisting through refresh or not will help us find and fix it, so please include that in the bug report!'
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
                      <li>${panelInfoItems[5]}</li>
                      <li>${panelInfoItems[6]}</li>
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
                    </ul>
                  </div>
                </div>
                ${nextBtn('cardsBtn')}
              </div>

              <div id="tour-slide-cardsBtn" class="slide">
                <div class="bubble">${cardsBtnBubble}</div>

                ${cardsBtnItems[0]}
                <div class="tour-img" id="img-buildingsBtn"></div>
                ${cardsBtnItems[1]}
                <div class="tour-img" id="img-buildings"></div>
                ${cardsBtnItems[2]}
                <div class="tour-remark">${cardsBtnItems[3]}</div>

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
                <div class="tour-remark">${playerPanelItems[4]}</div>

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

                  ${cookItems[2]}
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
    }
  );
});
