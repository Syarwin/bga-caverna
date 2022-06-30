define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  const WOOD_FIELDS = ['D75_WoodField', 'D75_WoodField2'];

  return declare('caverna.sow', null, {
    onEnteringStateSow(args) {
      this.gamedatas.gamestate.args.zones.forEach((zone) => {
        if (zone.type == 'fieldCard' && WOOD_FIELDS.includes(zone.id)) {
          this.createWoodFieldRadios(zone);
          return;
        }

        zone.sid = zone.type == 'fieldCard' ? zone.id : zone.x + '-' + zone.y;
        this.place('tplSowSwitch', zone, this.getSowZoneContainer(zone));
        document.getElementsByName('switch-' + zone.sid).forEach((radio) => {
          dojo.connect(radio, 'change', () => this.updateSowSwitches());
        });
      });
      this.updateSowSwitches();
    },

    getSowZoneContainer(zone) {
      if (zone.type == 'field') {
        return this.getCell(zone);
      } else {
        let elem = $(zone.id).querySelector('.player-card-field-cell');
        dojo.addClass(elem, 'active');
        return elem;
      }
    },

    forEachSowRadios(callback) {
      this.gamedatas.gamestate.args.zones.forEach((zone) => {
        document.getElementsByName('switch-' + zone.sid).forEach((radio) => {
          callback(radio, zone);
        });
      });
    },

    clearSowRadios() {
      dojo.query('.switch-wrapper').forEach(dojo.destroy);
      dojo.query('.player-card-field-cell').removeClass('active');
    },

    /**
     * Update sow radios depending on what is currently selected
     *  and toggle confirm button
     */
    updateSowSwitches() {
      // Compute selected crops
      let crops = { grain: 0, neutral: 0, vegetable: 0, wood: 0 };
      this.forEachSowRadios((radio) => {
        if (radio.checked) crops[radio.value]++;
      });

      // Update disabled states
      let args = this.gamedatas.gamestate.args;
      this.forEachSowRadios((radio, zone) => {
        if (radio.value != 'neutral')
          radio.disabled =
            (!radio.checked && crops[radio.value] == args[radio.value]) ||
            (zone.constraints && zone.constraints != radio.value);
      });

      // Update button
      dojo.destroy('btnConfirmSow');
      let total = crops.grain + crops.vegetable + (crops.wood > 0);
      if (total > 0 && total <= this.gamedatas.gamestate.args.max) {
        this.addPrimaryActionButton('btnConfirmSow', _('Confirm'), () => this.onClickConfirmSow());
      }
    },

    /**
     * Gather sow informations and send atomic action
     */
    onClickConfirmSow() {
      // Compute selected crops
      let crops = [];
      this.forEachSowRadios((radio, zone) => {
        if (radio.checked && radio.value != 'neutral') {
          crops.push({
            id: zone.uid,
            crop: radio.value,
          });
        }
      });

      this.takeAtomicAction('actSow', [crops]);
    },

    tplSowSwitch(zone) {
      let id = zone.sid;
      return this.formatStringMeeples(`
      <div class="switch-wrapper" id="switch-holder-${id}" data-id="${id}">
        <input type="radio" name="switch-${id}" class="switch-grain" id="switch-grain-${id}" value="grain" />
        <label for="switch-grain-${id}"> <GRAIN> </label>

        <input type="radio" name="switch-${id}" class="switch-neutral" checked id="switch-neutral-${id}" value="neutral" />
        <label for="switch-neutral-${id}"></label>

        <input type="radio" name="switch-${id}" class="switch-vegetable" id="switch-vegetable-${id}" value="vegetable" />
        <label for="switch-vegetable-${id}"> <VEGETABLE> </label>

        <div class="switch-ball"></div>
      </div>
      `);
    },

    /**
     * Return container for seeds, creating it if needed
     */
    getSeedContainer(field) {
      // TODO : handle card on which you can sow
      if (!$('meeple-' + field.id)) {
        if (!$(field.id)) {
          console.error('Trying to sow on a non-existing field', field);
          return 'game_play_area';
        } else {
          // Card field
          return $(field.id).querySelector('.resource-holder');
        }
      }

      // Create container if needed
      if (!$('seed-holder-' + field.id)) {
        this.place('tplSeedContainer', field, 'meeple-' + field.id);
      }

      return 'seed-holder-' + field.id;
    },

    tplSeedContainer(field) {
      return `
      <div class="seed-holder" id="seed-holder-${field.id}"></div>
      `;
    },

    /**
     * Sowing notification
     */
    notif_sow(n) {
      debug('Notif: sowing seeds', n);
      // Remove switches if needed
      this.clearSowRadios();

      let resources = [];
      n.args.sows.forEach((sow, i) => {
        if (sow.seed != null) {
          sow.seed.slideConfig = { delay: 200 * i };
          resources.push(sow.seed);
        }

        sow.crops.forEach((crop, j) => {
          crop.slideConfig = {
            delay: 200 * i + 100 * (j + 1),
            from: 'page-title',
          };
          resources.push(crop);
        });
      });

      this.slideResources(resources, (meeple) => meeple.slideConfig);
    },

    /********************************
     ********* D75_WoodField *********
     ********************************/
    createWoodFieldRadios(zone) {
      zone.sid = zone.uid;
      this.place('tplSowWoodSwitch', zone, this.getSowZoneContainer(zone));
      document.getElementsByName('switch-' + zone.sid).forEach((radio) => {
        dojo.connect(radio, 'change', () => this.updateSowSwitches());
      });
    },

    tplSowWoodSwitch(zone) {
      let id = zone.sid;
      return this.formatStringMeeples(`
      <div class="switch-wrapper wood-switch" id="switch-holder-${id}" data-id="${id}">
        <input type="radio" name="switch-${id}" class="switch-neutral" checked id="switch-neutral-${id}" value="neutral" />
        <label for="switch-neutral-${id}"></label>

        <input type="radio" name="switch-${id}" class="switch-wood" id="switch-wood-${id}" value="wood" />
        <label for="switch-wood-${id}"> <WOOD> </label>

        <div class="switch-ball"></div>
      </div>
      `);
    },
  });
});
