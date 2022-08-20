define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('caverna.actionCards', null, {
    /**
     * Setup all actions slot/cards
     */
    setupActionCards() {
      let nPlayers = Object.keys(this.gamedatas.players).length;
      let nTurns = nPlayers <= 2 ? 11 : 12;
      for (var i = 1; i <= nTurns; i++) {
        this.place('tplTurnContainer', i, 'central-board');
      }

      this.gamedatas.cards.visible.forEach((card) => {
        this.addActionCard(card);
      });
      this.setupActionCardsHelpers();
    },

    setupActionCardsHelpers() {
      return; // TODO
      // Tooltip on future rounds
      this.gamedatas.cards.help.forEach((card) => {
        card.description = this.formatCardDesc(card.desc);
        card.tooltipText = '';
        card.tooltipDescription = this.formatCardDesc(card.tooltipDesc);
      });
      this.updateActionCardsHelp();
    },

    updateActionCardsHelp() {
      return; // TODO

      let bounds = [1, 5, 8, 10, 12, 14, 15];
      for (var i = 0; i < bounds.length - 1; i++) {
        let lower = bounds[i],
          upper = bounds[i + 1];

        let html =
          '<div class="action-card-help-tooltip">' +
          this.gamedatas.cards.help.reduce((html, card) => {
            if ($(card.id)) return html;

            let turn = card.location.split('_')[1];
            return html + (lower <= turn && turn < upper ? this.tplActionCardTooltip(card) : '');
          }, '') +
          '</div>';

        for (var j = lower; j < upper; j++) {
          if (!$('turn_' + j)) {
            continue;
          }
          this.addTooltipHtml('turn-number-tooltipable-' + j, html);
        }
      }
    },

    /**
     * Main factory function for action card
     */
    addActionCard(card, flip = false) {
      // Add extra info
      card.description = this.formatCardDesc(card.desc);
      card.tooltipText = card.tooltip.map((s) => _(s)).join('<br />');
      card.tooltipDescription = card.description; // TODO : this.formatCardDesc(card.tooltipDesc);

      // Place it
      let addTooltip = () => {
        this.addCustomTooltip(card.id, this.tplActionCardTooltip(card));
      };

      if (card.component) {
        this.place('tplActionCard', card, 'central-board');
        addTooltip();
      } else {
        if (flip) {
          let target = $(card.location).querySelector('.turn-number');
          if (this.isFastMode()) {
            this.flipAndReplace(target, this.tplActionCard(card));
            addTooltip();
          } else {
            this.flipAndReplace(target, this.tplActionCard(card)).then(addTooltip);
          }
        } else {
          let target = $(card.location).querySelector('.turn-number');
          dojo.destroy(target); // Remove turn number slot
          this.place('tplActionCard', card, card.location);
          addTooltip();
        }
      }
    },

    /**
     * Format action card desc
     */
    formatCardDesc(desc) {
      return desc
        .map((s) => _(s))
        .map((s) => s.replace(/__([^_]+)__/g, '<span class="action-card-name-reference">$1</span>'))
        .map((s) => '<div>' + this.formatStringMeeples(s) + '</div>')
        .join('');
    },

    /**
     * When a new action card is revealed at the begining of a turn
     */
    notif_revealActionCard(n) {
      debug('Notif: revealing a new card', n);
      this.addActionCard(n.args.card, true);
      dojo.attr('game_play_area', 'data-turn', n.args.turn);
      this.updateActionCardsHelp();
    },

    /**
     * Slot for turn
     */
    tplTurnContainer(turn) {
      return (
        `
      <div class='action-card-wrapper turn-action-container' id='turn_${turn}'>
        <div class='turn-number'>
          <div class='help-marker' id='turn-number-tooltipable-${turn}'>
            <svg><use href="#help-marker-svg" /></svg>
          </div>
          <div class='turn-number-indication'>${turn}</div>
        </div>
        <div class='future-resources-holder'>
        ` +
        Object.values(this.gamedatas.players)
          .map(
            (player) =>
              `<div data-pid="${player.id}" data-color="${player.color}" class='resource-holder-update'></div>`,
          )
          .join('') +
        `
        </div>
      </div>`
      );
    },

    /**
     * Template for action card
     */
    tplActionCard(card) {
      return (
        `<div class='action-card-wrapper'>
      <div id="${card.id}" data-id="${card.id}" class="action-card-holder">
        <div class="dwarf-holder resource-holder-update" data-n="0"></div>
        <div class="action-card">
          <h4 class="action-header">${_(card.name)}</h4>
          <div class="action-background">
            <div class="action-desc">${card.description}</div>
          </div>
        </div>
        ` +
        (card.accumulate ? "<div class='resource-holder resource-holder-update'></div>" : '') +
        `
      </div>
    </div>`
      );
    },

    /**
     * Template for action card tooltip
     */
    tplActionCardTooltip(card) {
      return `
      <div class="action-card-tooltip">
        <div class="action-holder">
          <div data-id="${card.id}" class="action-card-holder">
            <div class="action-card">
              <h4 class="action-header">${_(card.name)}</h4>
              <div class="action-background">
                <div class="action-desc">${card.tooltipDescription}</div>
              </div>
            </div>
          </div>
        </div>
        <div class="card-tooltip-text">
          ${card.tooltipText}
        </div>
      </div>
      `;
    },

    /**
     * Make cards selectable
     */
    promptActionCard(cards, callback, allCards = []) {
      if (this.isFastMode()) return;

      allCards.forEach((cId) => dojo.addClass(cId, 'unselectable'));
      cards.forEach((cId) => {
        this.onClick(cId, () => callback(cId));
      });
    },
  });
});
