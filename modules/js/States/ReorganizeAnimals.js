define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  const ANIMALS = ['sheep', 'pig', 'cattle'];

  return declare('caverna.reorganize', null, {
    /**
     * Create dropzones when opening the page
     */
    setupAnimalsDropZones() {
      this.forEachPlayer((player) => this.updateAnimalsDropZones(player.id));
    },

    getAnimalsDropZones(pId) {
      return this.gamedatas.players[pId].board.dropZones;
    },

    /**
     * Update animal dropZones of a player according to gamedatas
     */
    updateAnimalsDropZones(pId) {
      // Delete previous controls
      dojo.query(`#board-${pId} .animals-control-wrapper`).forEach(dojo.destroy);
      dojo.query(`#cards-wrapper-${pId} .animals-control-wrapper`).forEach(dojo.destroy);

      // Create new ones
      let zones = this.getAnimalsDropZones(pId);
      zones.forEach((zone, i) => {
        zone.pId = pId;
        zone.cId = pId + '-' + i; // Control ID, must be unique on DOM
        zone.holder = this.computeDropZoneControlHolder(zone);
        if (zone.type == 'card') {
          // zone.control = this.place('tplReorganizeControl', zone, zone.holder);
          zone.control = this.place('tplReorganizeControl', zone, zone.holder.parentNode);
        } else {
          zone.control = this.place('tplReorganizeControl', zone, this.getAnimalControlContainer(zone));
        }
      });

      this.updateDropZonesStatus(false);
    },

    /**
     * Compute the nicest spot to put the control
     *  => take the most top-right location of the zone
     */
    computeDropZoneControlHolder(zone) {
      if (zone.type == 'room') return zone.locations[0];

      if (zone.type == 'card') return this.getMeepleContainer({ location: zone.card_id });

      return zone.locations.reduce(
        (acc, location) => {
          return location.x < acc.x || (location.x == acc.x && location.y > acc.y) ? acc : location;
        },
        { x: 0, y: 10 },
      );
    },

    /**
     * Special case of harvest, need to take child into account
     */
    updateHarvestAnimalCounters() {
      let pId = this.player_id;

      ANIMALS.forEach((type) => {
        let onBoard = this.countAnimalsOnBoard(type);
        let inReserve = this.countAnimalsInReserve(type);
        let total = onBoard + inReserve;
        if (total <= 2) {
          return; // No baby of this type
        }

        if (onBoard >= 2) {
          // Still enough parent to have a baby
          if (inReserve >= 1) {
            // At least one meeple in reserve => declare 1 as a baby
            this._playerCounters[pId][type].toValue(inReserve - 1);
            this._babyCounters[type].toValue(1);
          } else {
            this._babyCounters[type].toValue(0);
          }
        } else {
          // No more parent => put the baby in limbo
          this._playerCounters[pId][type].toValue(inReserve - 1);
          this._babyCounters[type].toValue(0);
        }
      });
    },

    /**
     * Update drop zones counters and "disabled" status
     */
    updateDropZonesStatus(preAnimation) {
      return; // TODO
      
      // Update zones capacity counters
      this.forEachPlayer((player) => {
        this.getAnimalsDropZones(player.id).forEach((zone) => {
          if (zone.cId == undefined) return;

          let animals = this.getAnimalsInZone(zone);
          $('zone-current-capacity-' + zone.cId).innerHTML =
            animals.sheep.length + animals.pig.length + animals.cattle.length;
        });
      });

      if (this.isSpectator || preAnimation || !this._canReorganize) return;

      // Update buttons states
      let reserve = {};
      if (this._isHarvest) this.updateHarvestAnimalCounters();
      ANIMALS.forEach(
        (type) =>
          (reserve[type] =
            this._playerCounters[this.player_id][type].getValue() +
            (this._isHarvest ? this._babyCounters[type].getValue() : 0)),
        //        (type) => (reserve[type] = $(`reserve_${this.player_id}_${type}`).querySelectorAll('.caverna-meeple').length),
      );

      this.getAnimalsDropZones(this.player_id).forEach((zone) => {
        if (zone.cId == undefined) return;

        let content = this.getAnimalsInZone(zone);

        if (zone.type == 'card') {
          if (zone.card_id == 'C11_WildlifeReserve') {
            // only 1 of each animal can be put
            ANIMALS.forEach((type) => {
              $(`ac-infty-${type}-${zone.cId}`).disabled = true;
              let animalLeft = reserve[type] > 0 || this.getMeeplesFromOtherZones(zone, type, 1).length > 0;
              $(`ac-plus-${type}-${zone.cId}`).disabled = content[type].length >= 1 || animalLeft == 0;
              $(`ac-minus-${type}-${zone.cId}`).disabled = content[type].length == 0;
            });
          } else if (zone.card_id == 'C86_LivestockFeeder') {
            let isMax = content.sheep.length + content.pig.length + content.cattle.length >= zone.capacity;
            // only 1 of each animal can be put
            ANIMALS.forEach((type) => {
              let row = $('animals-control-' + zone.cId).querySelector('.composition-type.composition-' + type);
              dojo.removeClass(row, 'hidden');
              let animalLeft = reserve[type] > 0 || this.getMeeplesFromOtherZones(zone, type, 1).length > 0;
              $(`ac-plus-${type}-${zone.cId}`).disabled = isMax || !animalLeft;
              $(`ac-infty-${type}-${zone.cId}`).disabled = isMax || !animalLeft;
              $(`ac-minus-${type}-${zone.cId}`).disabled = content[type].length == 0;
            });
          }
        } else {
          // let content = this.getAnimalsInZone(zone);
          let isMax = content.sheep.length + content.pig.length + content.cattle.length >= zone.capacity;
          let zoneType = ANIMALS.reduce((acc, type) => (content[type].length > 0 ? type : acc), null);
          dojo.attr('animals-control-' + zone.cId, 'data-type', zoneType);
          let nHidden = 0;
          ANIMALS.forEach((type) => {
            let row = $('animals-control-' + zone.cId).querySelector('.composition-type.composition-' + type);
            let animalLeft = reserve[type] > 0 || this.getMeeplesFromOtherZones(zone, type, 1).length > 0;
            if (content[type].length == 0) {
              let isHidden =
                !animalLeft ||
                (zoneType != null && type != zoneType) ||
                (zone.constraints !== undefined && !zone.constraints.includes(type));
              dojo.toggleClass(row, 'hidden', isHidden);
              nHidden += isHidden ? 1 : 0;
            } else {
              dojo.removeClass(row, 'hidden');
            }

            // Disable - buttons
            $(`ac-minus-${type}-${zone.cId}`).disabled = content[type].length == 0;

            // Disable + buttons
            $(`ac-plus-${type}-${zone.cId}`).disabled = isMax || !animalLeft;
            $(`ac-infty-${type}-${zone.cId}`).disabled = isMax || !animalLeft;
          });
          dojo.attr('animals-control-' + zone.cId, 'data-hidden', nHidden);
        }
      });

      // Update action button
      dojo.destroy('btnConfirmReorganization');
      let msg = _('Confirm reorganization'),
        btnType = 'Primary';
      if (reserve.sheep + reserve.pig + reserve.cattle > 0) {
        let animalsToCook = [];
        let animalsToDiscard = [];
        ANIMALS.forEach((type) => {
          // No extra animal of this type ? => return
          if (reserve[type] == 0) return;
          // Can't cook => everything goes to discard
          if (!this._possibleExchanges[type]) {
            animalsToDiscard.push(reserve[type] + '<' + type.toUpperCase() + '>');
          }
          // Can cook => we need to take into account the baby case during harvest
          else {
            let onBoard = this.countAnimalsOnBoard(type);
            if (this._isHarvest && this._babyCounters[type].getValue() > 0 && onBoard == 2) {
              animalsToCook.push(reserve[type] - 1 + '<' + type.toUpperCase() + '>');
              animalsToDiscard.push('1<' + type.toUpperCase() + '>');
            } else {
              animalsToCook.push(reserve[type] + '<' + type.toUpperCase() + '>');
            }
          }
        });
        let toCook = this.formatStringMeeples(animalsToCook.join(','));
        let toDiscard = this.formatStringMeeples(animalsToDiscard.join(','));

        if (animalsToDiscard.length > 0) {
          btnType = 'Danger';
          if (animalsToCook.length > 0) {
            msg = dojo.string.substitute(_('Confirm: cook ${animals} and discard ${lostAnimals}'), {
              animals: toCook,
              lostAnimals: toDiscard,
            });
          } else {
            msg = dojo.string.substitute(_('Confirm and discard ${animals}'), { animals: toDiscard });
          }
        } else {
          msg = dojo.string.substitute(_('Confirm and cook ${animals}'), { animals: toCook });
        }
      }

      this['add' + btnType + 'ActionButton']('btnConfirmReorganization', msg, () =>
        this.onClickConfirmReorganization(),
      );
    },

    /**
     * Update drop zone notification : whenever someone construct/build fence/stables
     */
    notif_updateDropZones(n) {
      debug('Notif: updating player drop zones', n);
      let pId = n.args.player_id;
      this.gamedatas.players[pId].board.dropZones = n.args.zones;
      this.updateAnimalsDropZones(pId);
    },

    /**
     * Main state where reorganizing happens
     */
    onEnteringStateReorganize(args) {
      this.enableAnimalControls(args.exchanges);
      this._isHarvest = args.harvest;
      if (args.harvest) {
        dojo.addClass('board-wrapper-' + this.player_id, 'harvest');
        this.updateHarvestAnimalCounters();
      }
      this.updateDropZonesStatus();
    },

    /**
     * Send data to server
     */
    onClickConfirmReorganization() {
      let meeplesOnBoard = [],
        meeplesOnReserve = [],
        meeplesOnCard = [];

      ANIMALS.forEach((type) => {
        // Meeples on board cells
        $('board-' + this.player_id)
          .querySelectorAll('.meeple-' + type)
          .forEach((meepleObj) => {
            let cell = meepleObj.closest('.board-cell');
            meeplesOnBoard.push({
              id: meepleObj.getAttribute('data-id'),
              x: cell.getAttribute('data-x'),
              y: cell.getAttribute('data-y'),
            });
          });

        // Meeples in reserve
        $(`reserve_${this.player_id}_${type}`)
          .querySelectorAll('.meeple-' + type)
          .forEach((meepleObj) =>
            meeplesOnReserve.push({
              id: meepleObj.getAttribute('data-id'),
            }),
          );

        // Meeples on specific cards

        if ($(`cards-wrapper-` + this.player_id).querySelector('#C11_WildlifeReserve') != undefined) {
          $(`C11_WildlifeReserve`)
            .querySelectorAll('.meeple-' + type)
            .forEach((meepleObj) => {
              if (meepleObj.getAttribute('data-id') != null) {
                meeplesOnCard.push({
                  id: meepleObj.getAttribute('data-id'),
                  card_id: 'C11_WildlifeReserve',
                });
              }
            });
        }

        if ($(`cards-wrapper-` + this.player_id).querySelector('#C86_LivestockFeeder') != undefined) {
          $(`C86_LivestockFeeder`)
            .querySelectorAll('.meeple-' + type)
            .forEach((meepleObj) => {
              if (meepleObj.getAttribute('data-id') != null) {
                meeplesOnCard.push({
                  id: meepleObj.getAttribute('data-id'),
                  card_id: 'C86_LivestockFeeder',
                });
              }
            });
        }
      });

      this.takeAtomicAction('actReorganize', [meeplesOnBoard, meeplesOnReserve, meeplesOnCard]);
    },

    /**
     * Reorganization notification
     */
    notif_reorganize(n) {
      debug('Notif: reorganization', n);
      // Skip notification for active player
      if (this.player_id == n.args.player_id) {
        this.notifqueue.setSynchronousDuration(10);
        return;
      }
      this.slideResources(n.args.meeples, {});
    },

    /**
     * Enable/disable all controls
     */
    enableAnimalControls(exchanges = {}) {
      if (this.isSpectator) return;

      this._canReorganize = true;
      this._openedAnimalControl = null;
      this._possibleExchanges = exchanges;
      dojo.addClass('board-wrapper-' + this.player_id, 'reorganizing');

      // Local connections to local handlers
      this._reorganizeHandlers = [];
      let onClick = (target, callback) => {
        this._reorganizeHandlers.push(dojo.connect($(target), 'click', callback));
      };
      onClick(document, () => this.closeAnimalsControl());

      // Add event handler on each control
      this.getAnimalsDropZones(this.player_id).forEach((zone) => {
        let control = $('animals-control-' + zone.cId);
        this.onClick('ac-open-' + zone.cId, (evt) => this.openAnimalControl(evt, zone));
        this.onClick(control, (evt) => evt.stopPropagation());

        // Buttons
        ANIMALS.forEach((type) => {
          ['minus', 'plus', 'infty'].forEach((operation) => {
            onClick(`ac-${operation}-${type}-${zone.cId}`, () => this.animalControlOperation(zone, operation, type));
          });
        });

        onClick('ac-clear-' + zone.cId, () => this.clearAnimalControl(zone));
      });
    },

    disableAnimalControls() {
      if (!this._canReorganize) return;

      this._canReorganize = false;
      this.closeAnimalsControl();
      dojo.removeClass('board-wrapper-' + this.player_id, 'reorganizing');
      this._reorganizeHandlers.forEach(dojo.disconnect);
    },

    /**
     * Open/close a specific control
     */
    openAnimalControl(evt, zone) {
      if (!this._canReorganize) {
        return;
      }

      if (evt) {
        evt.stopPropagation();
      }

      if (this._openedAnimalControl != null) {
        this.closeAnimalsControl();
      }
      dojo.addClass('animals-control-' + zone.cId, 'active');
      this._openedAnimalControl = zone;
    },

    closeAnimalsControl() {
      if (this._openedAnimalControl != null) {
        dojo.removeClass('animals-control-' + this._openedAnimalControl.cId, 'active');
        this._openedAnimalControl = null;
      }
    },

    /**
     * Handler to remove/add/infty a specific type to a zone
     */
    animalControlOperation(zone, operation, type) {
      debug('Reorganizing', zone, operation, type);
      let reserve = 'reserve_' + this.player_id + '_' + type;
      const duration = 300;

      if (operation == 'plus') {
        // Add a meeple of given type to the zone
        let meeples = this.getMeeplesInside(reserve, type, 1, false);
        if (meeples.length == 0) {
          meeples = this.getMeeplesFromOtherZones(zone, type, 1);
        }
        let loc = this.getNextDropLocationInZone(zone);
        // console.log('toto');
        // console.debug(loc);
        this.slideResources(
          meeples,
          {
            target: this.getAnimalHolder(loc),
            duration,
          },
          false,
        ).then(() => this.updateDropZonesStatus(false));
        this.updateDropZonesStatus(true);
      } else if (operation == 'minus') {
        // Remove a meeple of given type to the zone
        let loc = this.getNextDropLocationInZone(zone, true, false, type);
        let meeples = this.getMeeplesInside(this.getAnimalHolder(loc), type);
        this.slideResources(
          meeples,
          {
            target: reserve,
            duration,
          },
          false,
        ).then(() => this.updateDropZonesStatus(false));
        this.updateDropZonesStatus(true);
      } else if (operation == 'infty') {
        // Fill in until max capa or no more meeple
        let rest = zone.capacity - this.countAnimalsInZone(zone);
        let meeples = this.getMeeplesInside(reserve, type, rest, false);
        if (meeples.length < rest) {
          meeples = meeples.concat(this.getMeeplesFromOtherZones(zone, type, rest - meeples.length));
        }
        let iLoc = this.getNextDropLocationInZone(zone, false, true); // Get INDEX of location
        this.slideResources(
          meeples,
          (meeple, i) => ({
            target: this.getAnimalHolder(zone.locations[(iLoc + i) % zone.locations.length]),
            duration,
          }),
          false,
          false,
        ).then(() => this.updateDropZonesStatus(false));
        this.updateDropZonesStatus(true);
      }
    },

    /**
     * Remove all animals from a zone
     */
    clearAnimalControl(zone) {
      // Gather all meeples
      let animals = this.getAnimalsInZone(zone);
      let meeples = [];
      ANIMALS.forEach((type) => {
        animals[type].forEach((id) => {
          meeples.push({
            id: id,
            type: type,
          });
        });
      });

      // Slide them
      this.slideResources(
        meeples,
        (meeple) => ({
          target: 'reserve_' + this.player_id + '_' + meeple.type,
          duration: 300,
        }),
        false,
        false,
      );
    },

    /**
     * Get the holder of an animal control
     */
    getAnimalControlContainer(zone) {
      return this.getAnimalHolder(zone.holder, zone.pId).parentNode;
    },

    /**
     * Get the div that holds animal at a specific location
     */
    getAnimalHolder(loc, pId = null) {
      if (pId == null) {
        pId = this.player_id;
      }

      if (loc.hasOwnProperty('type') && loc.type == 'card') {
        return this.computeDropZoneControlHolder(loc);
      }
      return this.getCell(loc, pId).querySelector('.animal-holder');
    },

    /**
     * Extract the first meeple inside a container, of given type
     */
    getMeeplesInside(container, type, n = 1, error = true) {
      let meeples = Array.from($(container).querySelectorAll('.meeple-' + type)).slice(0, n);
      if (meeples.length == 0) {
        if (error) {
          console.error('Trying to get meeple inside an empty container', container, type);
          return null;
        } else {
          return [];
        }
      }

      return meeples.map((meeple) => ({
        id: meeple.getAttribute('data-id'),
        type: meeple.getAttribute('data-type'),
      }));
    },

    /**
     * Find meeples in other zones to add them
     */
    getMeeplesFromOtherZones(excludedZone, type, n = 1) {
      let zones = this.getAnimalsDropZones(this.player_id).filter((zone) => zone.cId != excludedZone.cId);
      const weights = {
        room: 1,
        stable: 2,
        pasture: 3,
      };
      zones.forEach((zone) => {
        zone.typeWeight = weights[zone.type];
      });
      zones.sort((a, b) => a.typeWeight - b.typeWeight);

      let meeples = [];
      zones.forEach((zone) => {
        zone.locations.forEach((loc) => {
          if (meeples.length == n) return;

          meeples = meeples.concat(this.getMeeplesInside(this.getAnimalHolder(loc), type, n - meeples.length, false));
        });
      });

      return meeples;
    },

    /**
     * Compute the next location in which we are going to put the animal
     *   in a given zone
     */
    getNextDropLocationInZone(zone, previous = false, index = false, type = null) {
      let counts = zone.locations.map((location) => this.countAnimalsAtLocation(location, null, type));

      /// Compute min/max number of animals in zone
      let min = Math.min(...counts),
        max = Math.max(...counts);
      let filtered = Object.keys(counts).filter(
        (i) => (previous && counts[i] == max) || (!previous && counts[i] == min),
      );

      let zoneIndex = previous ? filtered[filtered.length - 1] : filtered[0];
      return index ? zoneIndex : zone.locations[zoneIndex];
    },

    /**
     * Compute number of animals at given zone/location
     */
    getAnimalsInZone(zone) {
      let animals = {
        sheep: [],
        pig: [],
        cattle: [],
      };

      zone.locations.forEach((loc) => {
        this.getAnimalHolder(loc, zone.pId)
          .querySelectorAll('.caverna-meeple')
          .forEach((meeple) => {
            animals[meeple.getAttribute('data-type')].push(meeple.getAttribute('data-id'));
          });
      });

      return animals;
    },

    countAnimalsInZone(zone) {
      let content = this.getAnimalsInZone(zone);
      return content.sheep.length + content.pig.length + content.cattle.length;
    },

    countAnimalsAtLocation(loc, pId = null, type = null) {
      let classType = type == null ? '' : '.meeple-' + type;
      return this.getAnimalHolder(loc, pId).querySelectorAll('.caverna-meeple' + classType).length;
    },

    countAnimalsInReserve(type) {
      return $(`reserve_${this.player_id}_${type}`).querySelectorAll('.caverna-meeple').length;
    },

    countAnimalsOnBoard(type) {
      return $(`board-${this.player_id}`).querySelectorAll('.caverna-meeple.meeple-' + type).length;
    },

    /**
     * Animal controls template : basically 3*3 buttons
     */
    tplReorganizeControl(zone) {
      let id = zone.cId;
      return (
        `
      <div class="animals-control-wrapper">
        <div class="animals-control" id="animals-control-${id}">
          ` +
        (zone.pId == this.player_id
          ? `
          <div class="ac-open" id="ac-open-${id}">
            <svg><use href="#expand-marker-svg" /></svg>
          </div>
          <div class="ac-clear" id="ac-clear-${id}">
            ${_('Clear')}
          </div>
          <div class="ac-composition" id="ac-composition-${id}">
            <div class="composition-type composition-sheep">
              <button class="composition-minus" id="ac-minus-sheep-${id}"><div></div></button>
              <button class="composition-plus"  id="ac-plus-sheep-${id}" ><div></div></button>
              <button class="composition-infty" id="ac-infty-sheep-${id}"><div></div></button>
            </div>

            <div class="composition-type composition-pig">
              <button class="composition-minus" id="ac-minus-pig-${id}"><div></div></button>
              <button class="composition-plus"  id="ac-plus-pig-${id}" ><div></div></button>
              <button class="composition-infty" id="ac-infty-pig-${id}"><div></div></button>
            </div>

            <div class="composition-type composition-cattle">
              <button class="composition-minus" id="ac-minus-cattle-${id}"><div></div></button>
              <button class="composition-plus"  id="ac-plus-cattle-${id}" ><div></div></button>
              <button class="composition-infty" id="ac-infty-cattle-${id}"><div></div></button>
            </div>
          </div>
          `
          : '') +
        `
        <div class="zone-capacity">
          <span id="zone-current-capacity-${id}">0</span>
          /
          <span>${zone.capacity}</span>
        </div>
        </div>
      </div>
      `
      );
    },
  });
});
