define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  const RESOURCES = ['wood', 'clay', 'reed', 'stone', 'grain', 'vegetable', 'food', 'sheep', 'pig', 'cattle'];
  const ANIMALS = ['sheep', 'pig', 'cattle'];
  const BREAD = 2;
  return declare('caverna.exchange', null, {
    /**
     * Entering the exchange state : create modal and init selection
     */
    onEnteringStateExchange(args) {
      this._possibleExchanges = args.exchanges;
      this._originalReserve = args.resources;
      this._currentExchanges = []; // Stack of exchanges we want to make
      this._exchangeTrigger = args.trigger;
      // this._harvestMinFood = args.harvestMinFood;
      this._extraAnimals = args.extraAnimals;
      this._mandatoryExchange = args.mandatory;

      if (!args.automaticAction) {
        // Setup and display dialog
        this.addPrimaryActionButton('btnShowPossibleExchanges', _('Show possible exchanges'), () =>
          this.openExchangeModal(),
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

      /* Is it harvest ??
      if (this._harvestMinFood != 0) {
        dojo.place(
          '<h3>' +
            _('Food needed for your family:') +
            ' ' +
            this._harvestMinFood +
            this.formatStringMeeples('<FOOD>') +
            '</h3>',
          'exchanges-container',
          'before',
        );
      }
      */

      // Has extra animals ?
      if (this._extraAnimals.sheep + this._extraAnimals.pig + this._extraAnimals.cattle != 0) {
        dojo.place(
          '<h3>' + _('Excess animals in reserve:') + ' ' + this.computeExtraAnimalsDesc() + '</h3>',
          'exchanges-container',
          'before',
        );
      }

      this.updateExchangeCounters();
    },

    /**
     * Open exchange modal
     */
    openExchangeModal() {
      this._exchangeDialog.show();
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
          true,
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
     * Confirm the exchanges
     */
    confirmExchanges() {
      this.takeAtomicAction('actExchange', [this._currentExchanges]);
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
          'exchanges-dialog-footer',
        );
      }

      dojo.destroy('btnConfirmExchange');
      dojo.destroy('btnPassExchange');

      let begging = 0; //this._harvestMinFood - newReserve['food'];
      let extraAnimals = this.computeExtraAnimalsDesc();
      if (begging > 0) {
        this.addDangerActionButton(
          'btnConfirmExchange',
          dojo.string.substitute(this.formatStringMeeples(_('Confirm and take ${begging} <BEGGING>')), { begging }),
          () => this.confirmExchanges(),
          'exchanges-dialog-footer',
        );
      } else if (extraAnimals != '' && this._mandatoryExchange) {
        this.addDangerActionButton(
          'btnConfirmExchange',
          dojo.string.substitute(this.formatStringMeeples(_('Confirm and discard ${extraAnimals}')), { extraAnimals }),
          () => this.confirmExchanges(),
          'exchanges-dialog-footer',
        );
      } else if (this._currentExchanges.length > 0) {
        this.addPrimaryActionButton(
          'btnConfirmExchange',
          _('Confirm'),
          () => this.confirmExchanges(),
          'exchanges-dialog-footer',
        );
      } else {
        if (
          (this._exchangeTrigger != BREAD && !this._mandatoryExchange) ||
          this.gamedatas.gamestate.args.optionalAction
        ) {
          this.addSecondaryActionButton(
            'btnPassExchange',
            _('Pass'),
            () => this.takeAction('actPassOptionalAction'),
            'exchanges-dialog-footer',
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
      let source = exchange.source ? _(exchange.source) : '';
      let arrowN = exchange.max != 9999 ? '-' + exchange.max + 'X' : '';
      let desc = this.formatStringMeeples(
        this.formatResourceArray(exchange.from, false) +
          `<ARROW${arrowN}>` +
          this.formatResourceArray(exchange.to, false),
      );

      return `
          <div class='exchange-source'>${source}</div>
          <button class='exchange-desc' id="exchange-${exchange.id}">${desc}</button>
      `;
    },
  });
});
