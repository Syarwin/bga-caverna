define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  const PLAYER_RESOURCES = 109;
  const RESOURCES = [
    'wood',
    'stone',
    'ore',
    'ruby',
    'grain',
    'vegetable',
    'food',
    'begging',
    'sheep',
    'pig',
    'cattle',
    'donkey',
    'dog',
  ];
  const PERSONAL_RESOURCES = ['dwarf', 'stable'];
  const MAX_PERSONAL_RESOURCES = {
    stable: 3,
    dwarf: 5,
  };
  const ALL_RESOURCES = RESOURCES.concat(PERSONAL_RESOURCES);
  const ANIMALS = ['sheep', 'pig', 'cattle', 'donkey', 'dog'];
  const SCORE_CATEGORIES = [
    'fields',
    'pastures',
    'grains',
    'vegetables',
    'sheeps',
    'pigs',
    'cattles',
    'empty',
    'stables',
    'clayRooms',
    'stoneRooms',
    'dwarves',
    'beggings',
    'cards',
    'cardsBonus',
    'total',
  ];
  const SCORE_QTY_CATEGORIES = [
    'fields',
    'pastures',
    'grains',
    'vegetables',
    'sheeps',
    'pigs',
    'cattles',
    'empty',
    'stables',
    'clayRooms',
    'stoneRooms',
    'dwarves',
    'beggings',
  ];
  const SCORE_MULTIPLE_ENTRIES = ['cards', 'cardsBonus'];

  return declare('caverna.players', null, {
    // Utils to iterate over players array/object
    forEachPlayer(callback) {
      Object.values(this.gamedatas.players).forEach(callback);
    },

    getPlayerColor(pId) {
      return this.gamedatas.players[pId].color;
    },

    setupPlayers() {
      // Change No so that it fits the current player order view
      let currentNo = Object.values(this.gamedatas.players).reduce(
        (carry, player) => (player.id == this.player_id ? player.no : carry),
        0,
      );
      let nPlayers = Object.keys(this.gamedatas.players).length;
      this.forEachPlayer((player) => (player.order = (player.no + nPlayers - currentNo) % nPlayers));
      let orderedPlayers = Object.values(this.gamedatas.players).sort((a, b) => a.order - b.order);

      // Add player board and player panel
      orderedPlayers.forEach((player) => {
        this.place('tplPlayerBoard', player, 'player-boards');
        this.place('tplPlayerPanel', player, 'overall_player_board_' + player.id);
      });
      dojo.place('<div id="player-boards-separator"></div>', 'player-boards', 3);

      this.setupPlayersCounters();
      this.setupPlayersScores();
      // TODO ?? this.updateResourceBarsPositions();
      dojo.attr('game_play_area', 'data-players', Object.keys(this.gamedatas.players).length);
    },

    getCell(pos, pId = null) {
      if (pId == null) {
        pId = pos.pId ? pos.pId : this.player_id;
      }
      return document.querySelector(`#board-${pId} .board-cell[data-x='${pos.x}'][data-y='${pos.y}']`);
    },

    updateResourceBarsPositions() {
      this.forEachPlayer((player) => {
        dojo.place(
          `caverna-pannel-${player.id}`,
          (this.prefs[PLAYER_RESOURCES].value == 0 ? 'overall_player_board_' : 'resources-bar-holder-') + player.id,
        );
        dojo.toggleClass('resources-bar-holder-' + player.id, 'active', this.prefs[PLAYER_RESOURCES].value == 1);
      });
      this.updatePlayerBoardDimensions();
      this.updateDwarvesPlayerCounters();
    },

    /**
     * Create all the counters for player panels
     */
    setupPlayersCounters() {
      this._playerCounters = {};
      this._scoreCounters = {};
      this.forEachPlayer((player) => {
        this._playerCounters[player.id] = {};
        ALL_RESOURCES.forEach((res) => {
          this._playerCounters[player.id][res] = this.createCounter('resource_' + player.id + '_' + res);
        });
        this._scoreCounters[player.id] = this.createCounter('player_score_' + player.id);
      });
      this.updatePlayersCounters(false);

      // Setup baby counters
      // TODO
      if (!this.isSpectator && false) {
        this._babyCounters = {};
        ANIMALS.forEach((res) => {
          let brother = $(`resource_${this.player_id}_${res}`);
          dojo.place(
            this.formatStringMeeples(`
            <div class='baby-counter'>
              +
              <span id='resource_baby_${res}' class='resource_${res}'></span>
              <${res.toUpperCase()}>
            </div>
          `),
            brother.parentNode,
          );

          this._babyCounters[res] = this.createCounter('resource_baby_' + res);
        });
      }
    },

    /**
     * Update all the counters in player panels according to gamedatas
     */
    updatePlayersCounters(anim = true) {
      this.forEachPlayer((player) => {
        ALL_RESOURCES.forEach((res) => {
          let reserve = $(`reserve_${player.id}_${res}`);
          let meeples = reserve.querySelectorAll(`.meeple-${res}`);
          let value = meeples.length;
          if (PERSONAL_RESOURCES.includes(res)) {
            value = MAX_PERSONAL_RESOURCES[res] - value;
          }

          this._playerCounters[player.id][res][anim ? 'toValue' : 'setValue'](value);
          dojo.attr(reserve.parentNode, 'data-n', value);
        });
      });
      this.updateAnimalsPlayerCounters();
      this.updateDwarvesPlayerCounters();
      this.updatePlayersHarvestCosts();
    },

    updateAnimalsPlayerCounters(type = null) {
      let types = type == null ? ANIMALS : [type];
      this.forEachPlayer((player) => {
        // Update summary animal counters
        let board = $(`board-wrapper-${player.id}`);
        types.forEach((res) => {
          let meeples = board.querySelectorAll(`.animal-holder .meeple-${res}`);
          let n = meeples.length + this._playerCounters[player.id][res].getValue();
          $(`board_resource_${player.id}_${res}`).innerHTML = n;
        });
      });

      if (this._isHarvest) {
        this.updateHarvestAnimalCounters();
      }
    },

    updateDwarvesPlayerCounters() {
      this.forEachPlayer((player) => {
        let boardWrapper = $(`board-wrapper-${player.id}`);
        let meeples = boardWrapper.querySelectorAll(`:not(.actionCard):not(.dwarf-holder) > .meeple-dwarf`);
        let caravanMeeples = boardWrapper.querySelectorAll(`[data-id='B10_Caravan'] .meeple-dwarf`);

        dojo.attr(`board_resource_${player.id}_dwarf`, 'data-n', meeples.length + caravanMeeples.length);
      });
    },

    updatePlayersHarvestCosts() {
      this.forEachPlayer((player) => {
        $(`resource_${player.id}_food`).setAttribute('data-harvest', '/' + player.harvestCost);
      });
    },

    notif_updateHarvestCosts(n) {
      this.forEachPlayer((player) => {
        player.harvestCost = n.args.costs[player.id];
      });
      this.updatePlayersHarvestCosts();
    },

    /*
     * Player panel tpl
     */
    tplPlayerPanel(player) {
      return (
        `
      <div class="caverna-player-pannel" id="caverna-pannel-${player.id}" data-color="${player.color}">
        <div class="caverna-first-player-holder" id="reserve_${player.id}_firstPlayer"></div>
        <div class="player-panel-resources">
          <div class="player-reserve" id="reserve-${player.id}"></div>
        ` +
        RESOURCES.map((res) => (ANIMALS.includes(res) ? '' : this.tplResourceCounter(player, res))).join('') +
        `
        </div>
        <div class="player-panel-board-resources">
        ` +
        // Specific counters for animals on board or reserve
        ANIMALS.map((res) => this.tplResourceCounter(player, res, 'board_')).join('') +
        // Specific div to show remeaning dwarves
        this.formatStringMeeples(`
            <div class='player-resource resource-dwarf' id='board_resource_${player.id}_dwarf'>
              <FARMER><FARMER><FARMER><FARMER><FARMER>
            </div>
          `) +
        `
        </div>
        <div class="player-panel-personal-resources">
        ` +
        ['dwarf', 'stable'].map((res) => this.tplResourceCounter(player, res)).join('') +
        `
        </div>
      </div>
      `
      );
    },

    tplResourceCounter(player, res, prefix = '') {
      let iconName = res.toUpperCase();
      if (res == 'stable') {
        iconName = 'BARN';
      }

      return this.formatStringMeeples(`
        <div class='player-resource resource-${res}'>
          <span id='${prefix}resource_${player.id}_${res}' class='${prefix}resource_${res}'></span>
          <${iconName}>
          <div class='reserve' id='${prefix}reserve_${player.id}_${res}'></div>
        </div>
      `);
    },

    /*******************
     ****** SCORES *****
     ******************/
    /*
     * Display a table with a nice overview of current situation for everyone
     */
    setupScoresModal() {
      let content = this.format_string(jstpl_scoresModal, {
        fields: _('Fields'),
        pastures: _('Pastures'),
        grains: _('Grain'),
        vegetables: _('Vegetables'),
        sheeps: _('Sheep'),
        pigs: _('Wild boar'),
        cattles: _('Cattle'),
        empty: _('Unused spaces'),
        stables: _('Fenced stables'),
        clayRooms: _('Clay hut rooms'),
        stoneRooms: _('Stone house rooms'),
        dwarves: _('Family members'),
        cards: _('Points for cards'),
        cardsBonus: _('Bonus point'),
        beggings: _('Beggar cards'),
        total: _('Total'),
      });

      this._scoresModal = new customgame.modal('showScores', {
        class: 'caverna_popin',
        closeIcon: 'fa-times',
        contents: content,
        closeAction: 'hide',
        scale: 0.8,
        breakpoint: 800,
        verticalAlign: 'flex-start',
      });

      // Create columns
      this.forEachPlayer((player) => {
        dojo.place(`<th style='color:#${player.color}'>${player.name}</th>`, 'scores-headers');
        SCORE_CATEGORIES.forEach((row) => {
          let scoreElt = '<div><span id="score-' + player.id + '-' + row + '"></span><i class="fa fa-star"></i></div>';
          let addClass = '';

          // Scoring quantity ? Add a counter for the quantity
          if (SCORE_QTY_CATEGORIES.includes(row)) {
            let mapping = {
              empty: 'empty',
              clayRooms: 'room_clay',
              stoneRooms: 'room_stone',
              stables: 'barn',
            };
            let iconName = mapping[row] || row.slice(0, -1);
            if (iconName == 'field') iconName = 'field_icon';

            addClass = 'qty-scoring-entry';
            scoreElt =
              `<div>
              <span id="score-quantity-${player.id}-${row}"></span>
              <div class="meeple-container">
                <div class="caverna-meeple meeple-${iconName}" data-color="${player.color}">
                </div>
              </div>
            </div>` + scoreElt;
          }

          // Wrap that into a scoring entry
          scoreElt = `<div class="scoring-entry ${addClass}">${scoreElt}</div>`;

          if (SCORE_MULTIPLE_ENTRIES.includes(row)) {
            scoreElt += `<div class="scoring-subentries" id="score-subentries-${player.id}-${row}"></div>`;
          }

          dojo.place(`<td>${scoreElt}</td>`, 'scores-row-' + row);
        });
      });

      $('show-scores').addEventListener('click', () => this.showScoresModal());
      this.addTooltip('show-scores', '', _('Show scoring details.'));
      if (this.gamedatas.scores === null) {
        dojo.style('show-scores', 'display', 'none');
      }
    },

    showScoresModal() {
      this._scoresModal.show();
    },

    onEnteringStateGameEnd() {
      this.showScoresModal();
      dojo.style('show-scores', 'display', 'block');
    },

    /**
     * Create score counters
     */
    setupPlayersScores() {
      this._scoresCounters = {};
      this._scoresQtyCounters = {};

      this.forEachPlayer((player) => {
        this._scoresCounters[player.id] = {};
        this._scoresQtyCounters[player.id] = {};

        SCORE_CATEGORIES.forEach((category) => {
          this._scoresCounters[player.id][category] = this.createCounter('score-' + player.id + '-' + category);

          if (SCORE_QTY_CATEGORIES.includes(category)) {
            this._scoresQtyCounters[player.id][category] = this.createCounter(
              'score-quantity-' + player.id + '-' + category,
            );
          }
        });
      });

      this.updatePlayersScores(false);
    },

    /**
     * Update score counters
     */
    updatePlayersScores(anim = true) {
      return; // TODO
      
      if (this.gamedatas.scores !== null) {
        this.forEachPlayer((player) => {
          SCORE_CATEGORIES.forEach((category) => {
            let value =
              category == 'total'
                ? this.gamedatas.scores[player.id]['total']
                : this.gamedatas.scores[player.id][category]['total'];
            this._scoresCounters[player.id][category][anim ? 'toValue' : 'setValue'](value);

            if (SCORE_QTY_CATEGORIES.includes(category)) {
              let n = this.gamedatas.scores[player.id][category]['entries'][0]['quantity'];
              this._scoresQtyCounters[player.id][category][anim ? 'toValue' : 'setValue'](n);
            }

            if (SCORE_MULTIPLE_ENTRIES.includes(category)) {
              let container = $(`score-subentries-${player.id}-${category}`);
              dojo.empty(container);
              this.gamedatas.scores[player.id][category]['entries'].forEach((entry) => {
                dojo.place(
                  `<div class="scoring-subentry">
                  <div>${_(entry.source)}</div>
                  <div>
                    ${entry.score}
                    <i class="fa fa-star"></i>
                  </div>
                </div>`,
                  container,
                );
              });
            }
          });
          if (this._scoreCounters[player.id] !== undefined) {
            this._scoreCounters[player.id].toValue(this.gamedatas.scores[player.id].total);
          }
        });
      }
    },

    /**
     * Notification for live scoring
     */
    notif_updateScores(n) {
      debug('Notif: updating scores', n);
      this.gamedatas.scores = n.args.scores;
      this.updatePlayersScores();
    },
  });
});
