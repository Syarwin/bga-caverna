define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  return declare('caverna.specialEffect', null, {
    _specialStorage: {},
    /**
     * Entering the exchange state : create modal and init selection
     */
    onEnteringStateSpecialEffect(args) {
      if (args.description) {
        this.gamedatas.gamestate.descriptionmyturn = args.descriptionmyturn;
        this.gamedatas.gamestate.description = this.format_string_recursive(args.description, {
          actplayer: this.gamedatas.players[this.getActivePlayerId()].name,
        });
        this.updatePageTitle();
      }
      if (!this.isCurrentPlayerActive()) {
        dojo.destroy('btnPassAction');
        dojo.destroy('btnRestartTurn');
        return;
      }

      let method = args.cardId;
      if (this[method] != undefined) {
        this[method](args);
      }
    },

    A112_ScytheWorker(args) {
      this.promptPlayerBoardZones(args.zones, 1, 15, (zones) => this.takeAtomicAction('actA112', [zones]));
    },

    A71_ClearingSpade(args) {
      // Separate a set of zone into sources/targets
      let separate = (zones) => {
        let s = [],
          t = [];
        zones.forEach((zone) => {
          if (args.sources.includes(zone)) s.push(zone);
          else t.push(zone);
        });
        return [s, t];
      };

      this.promptPlayerBoardZones(
        args.sources.concat(args.targets),
        1,
        2,
        (zones) => this.takeAtomicAction('actA71', separate(zones)),
        (zones) => {
          let fzones = separate(zones);
          return fzones[0].length == fzones[1].length; // Same number of sources and targets
        },
      );
    },

    C104_Collector(args) {
      this._specialStorage = { nb: args.nb, resource: [] };
      ['food', 'wood', 'clay', 'stone', 'reed', 'grain', 'vegetable', 'sheep', 'pig', 'cattle'].forEach((resource) => {
        this.addPrimaryActionButton(
          resource + '-button',
          this.formatStringMeeples('<' + resource.toUpperCase() + '>'),
          () => this.C104_select(resource),
        );
      });
    },

    C104_select(resource) {
      if (this._specialStorage.resource.includes(resource)) return;
      this._specialStorage.resource.push(resource);
      dojo.addClass(resource + '-button', 'disabled');
      if (this._specialStorage.resource.length == this._specialStorage.nb) {
        this.addPrimaryActionButton('btnConfirmC104', _('Confirm'), () => {
          this.takeAtomicAction('actC104', [this._specialStorage.resource]);
        });
      }

      this.addSecondaryActionButton('btnClearC104', _('Clear'), () => {
        this._specialStorage.resource = [];
        dojo.query('#customActions .action-button').removeClass('disabled');
        dojo.destroy('btnClearC104');
        dojo.destroy('btnConfirmC104');
      });
    },

    D132_HideFarmer(args) {
      for (let i = 0; i <= args.max; i++) {
        let amount = i;
        this.addPrimaryActionButton(amount + '-button', amount, () => this.takeAtomicAction('actD132', [amount]));
      }
    },
  });
});
