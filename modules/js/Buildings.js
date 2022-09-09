define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  function isVisible(elem) {
    return !!(elem.offsetWidth || elem.offsetHeight || elem.getClientRects().length);
  }

  return declare('caverna.buildings', null, {
    setupBuildings() {
      // Construct buildings boards
      // prettier-ignore
      const buildingsBoards = {
        dwellings : ['D_Dwelling', 'D_SimpleDwelling1', 'D_SimpleDwelling2', 'D_MixedDwelling', 'D_CoupleDwelling', 'D_AddDwelling', 'G_CuddleRoom', 'G_BreakfastRoom', 'G_StubbleRoom', 'G_WorkRoom', 'G_GuestRoom', 'G_OfficeRoom'],
        materials : ['G_Carpenter', 'G_StoneCarver', 'G_Blacksmith', 'G_Miner', 'G_Builder', 'G_Trader', 'G_WoodSupplier', 'G_StoneSupplier', 'G_RubySupplier', 'G_DogSchool', 'G_Quarry', 'G_Seam'],
        food : ['G_SlaughteringCave', 'G_CookingCave', 'G_WorkingCave', 'G_MiningCave', 'G_BreedingCave', 'G_PeacefulCave', 'Y_WeavingParlor', 'Y_MilkingParlor', 'Y_StateParlor', 'Y_HuntingParlor', 'Y_BeerParlor', 'Y_BlacksmithingParlor'],
        bonus : ['Y_StoneStorage', 'Y_OreStorage', 'Y_SparePartStorage', 'Y_MainStorage', 'Y_WeaponStorage', 'Y_SuppliesStorage', 'Y_BroomChamber', 'Y_TreasureChamber', 'Y_FoodChamber', 'Y_PrayerChamber', 'Y_WritingChamber', 'Y_FodderChamber'],
      };
      // prettier-ignore
      const advancedBuildings = ['D_MixedDwelling', 'D_CoupleDwelling', 'D_AddDwelling', 'G_WorkRoom', 'G_GuestRoom', 'G_OfficeRoom', 'G_Miner', 'G_Builder', 'G_Trader', 'G_DogSchool', 'G_Quarry', 'G_Seam', 'G_MiningCave', 'G_BreedingCave', 'G_PeacefulCave', 'Y_StateParlor', 'Y_BlacksmithingParlor', 'Y_SparePartStorage', 'Y_SuppliesStorage', 'Y_BroomChamber', 'Y_PrayerChamber'];
      Object.keys(buildingsBoards).forEach((type) => {
        let board = dojo.place(
          `<div class='buildings-board' data-id='${type}'>
          <div class='building-board-left'></div>
          <div class='building-board-separator'></div>
          <div class='building-board-right'></div>
        </div>`,
          'buildings-container'
        );

        buildingsBoards[type].forEach((buildingId, i) => {
          if (this.gamedatas.beginner && advancedBuildings.includes(buildingId)) return;

          dojo.place(
            `<div class="building-placeholder" data-id="${buildingId}"></div>`,
            board.querySelector(i < 6 ? '.building-board-left' : '.building-board-right')
          );
        });

        dojo.place(
          `<div id='show-building-board-${type}' class='building-board-button' data-id='${type}'><i class="fa fa-times" aria-hidden="true"></i></div>`,
          'floating-building-buttons'
        );
        $(`show-building-board-${type}`).addEventListener('click', (evt) => this.goToBuildingBoard(type, evt));
      });

      //this.setupMajorsImprovements();
      this.updateBuildings();
      this._selectableBuildings = [];
      this._onSelectBuildingCallback = null;
    },

    goToBuildingBoard(type, evt = null) {
      if (evt) evt.stopPropagation();
      let pref = 0; // TODO
      if (pref == 0) {
        // Floating container
        if (this._floatingContainerOpen == type) {
          delete $('floating-building-boards-wrapper').dataset.open;
          this._floatingContainerOpen = null;
        } else {
          $('floating-building-boards-wrapper').dataset.open = type;
          this._floatingContainerOpen = type;
        }
      } else if (pref == 1) {
        // // Modal container
        // if (this._modalContainerOpen == company.id) {
        //   delete $('modal-company-boards-wrapper').dataset.open;
        //   this._modalContainerOpen = null;
        //   this._companiesModal.hide();
        // } else {
        //   $('modal-company-boards-wrapper').dataset.open = company.id;
        //   if (this._modalContainerOpen == null) {
        //     this._companiesModal.show();
        //   }
        //   this._modalContainerOpen = company.id;
        // }
      } else if (t.pref == 2) {
        // Below map
        // window.scrollTo(0, $(`company-board-${company.id}`).getBoundingClientRect()['top'] - 30);
      }
    },

    updateBuildings() {
      // This function is refreshUI compatible
      dojo.query('.office-room').removeClass('office-room');
      let buildingIds = this.gamedatas.buildings.map((building) => {
        this.loadSaveBuilding(building);
        // Create the building if needed
        if (!$(`building-${building.id}`)) {
          this.addBuilding(building);
        }
        // Move the building if not correct container
        let o = $(`building-${building.id}`);
        let container = this.getBuildingContainer(building);
        console.log(building, container);
        if (o.parentNode != $(container)) {
          dojo.place(o, container, 'first');
        }
        if (building.type == 'G_OfficeRoom' && building.location == 'inPlay') {
          $(`board-${building.pId}`).classList.add('office-room');
        }

        return parseInt(building.id);
      });

      // All the buildings not in specified list must be destroyed
      document.querySelectorAll('.caverna-building').forEach((oBuilding) => {
        if (!buildingIds.includes(parseInt(oBuilding.getAttribute('data-id')))) {
          dojo.destroy(oBuilding);
        }
      });
    },

    /**
     * Smarter buffering of building details (to not resend them over notification again)
     */
    loadSaveBuilding(building) {
      if (building.desc) {
        // Building contains all info => save it in buildingStorage
        building.description = this.formatCardDesc(building.desc);
        this._buildingStorage[building.id] = building;
      } else {
        // Building is missing information : load it from buildingStorage
        let infos = {};
        if (this._buildingStorage[building.id] === undefined) {
          if (building.type == 'D_Dwelling') {
            infos = this._buildingStorage[1];
          } else {
            console.error('Missing building informations :', building);
            return;
          }
        } else {
          infos = this._buildingStorage[building.id];
        }

        ['desc', 'extraVp', 'name', 'tooltip', 'type', 'vp'].forEach((info) => (building[info] = infos[info]));
      }
    },

    /**
     * Add a building to its default location
     */
    addBuilding(building, container = null) {
      this.loadSaveBuilding(building);

      if (container == null) {
        container = this.getBuildingContainer(building);
      }
      let oBuilding = dojo.place(this.tplBuilding(building), container, 'first');
      this.addCustomTooltip(`building-${building.id}`, this.tplBuildingTooltip(building));
      $(`building-${building.id}`).addEventListener('click', () => this.showBuildingDetails(building));
    },

    getBuildingContainer(building) {
      if (building.location == 'inPlay') {
        // Played building => on the board
        return this.getCell(building);
      } else {
        return $('buildings-container').querySelector(`.building-placeholder[data-id="${building.type}"]`);
      }

      console.error('Trying to get container of a building', building);
      return $('game_play_area');
    },

    /**
     * Template for all "player" buildings (Improvements and Occupations)
     */
    tplBuilding(building, tooltip = false) {
      let uid = (tooltip ? 'tooltip_building-' : 'building-') + building.id;
      let type = building.type.split('_')[1];

      return `<div id="${uid}" data-id='${building.id}' data-type='${type}' class='caverna-building'>
          <div class='building-resizable'>
            <div class='building-title'>
              ${_(building.name)}
            </div>
            <div class='building-desc'>${building.description}</div>
          </div>
        </div>`;
    },

    /**
     * Template for building tooltip
     */
    tplBuildingTooltip(building) {
      return `
      <div class="building-tooltip">
        <div class="building-holder">
          ${this.tplBuilding(building, true)}
        </div>
        <div class="building-tooltip-text">
          ${this.formatCardDesc(building.tooltip)}
        </div>
      </div>
      `;
    },

    showBuildingDetails(building, notif = null) {
      let selectable = this._selectableBuildings.includes(building.id);

      let footer = '';
      if (selectable || notif !== null) {
        footer = `<div class="building-details-footer" id='building-${building.id}-details-footer'></div>`;
      }
      let title =
        notif == null
          ? _(building.name)
          : this.substranslate(_('${player_name} furnishes its cavern with ${building_name}'), {
              player_name: this.coloredPlayerName(notif.args.player_name),
              building_name: _(building.name),
            });

      let dialog = new customgame.modal('buildingDetail' + building.id, {
        class: 'cavernaBuilding_popin',
        closeIcon: 'fa-times',
        autoShow: true,
        contents: `<div class="building-detail">${this.tplBuildingTooltip(building)}</div>${footer}`,
        verticalAlign: 'flex-start',
        scale: 0.8,
        title: title,
        breakpoint: 600,
      });

      if (selectable) {
        this.addPrimaryActionButton(
          `btnSelectBuilding${building.id}`,
          _('Furnish your cavern with this building'),
          () => {
            dialog.destroy();
            this._onSelectBuildingCallback(building.id);
          },
          `building-${building.id}-details-footer`
        );
      } else if (notif !== null) {
        this.addPrimaryActionButton(
          `btnDoneReadingDetails`,
          _('Ok'),
          () => {
            dialog.destroy();

            let config = {};
            let o = $(`building-${building.id}`);
            if (!this.isFastMode() && !isVisible(o)) {
              config.from = $(`floating-building-buttons`);
            }
            this.slide(o, this.getBuildingContainer(building), config).then(() =>
              this.notifqueue.setSynchronousDuration(10)
            );
          },
          `building-${building.id}-details-footer`
        );
      }
    },

    /**
     * Prompt current player to pick a building
     */
    promptBuilding(buildings, callback) {
      if (this.isFastMode()) return;

      this._selectableBuildings = buildings.map((id) => parseInt(id));
      this._onSelectBuildingCallback = callback;
      buildings.forEach((buildingId) => $(`building-${buildingId}`).classList.add('selectable'));
      this.goToBuildingBoard('dwellings');
    },

    /*
     * Notification when someone bought a building
     */
    notif_furnish(n) {
      debug('Notif: buying a building', n);
      let building = n.args.building;
      this.loadSaveBuilding(building);
      this._selectableBuildings = [];

      if (building.type == 'D_Dwelling' && !$(`building-${building.id}`)) {
        this.addBuilding(
          building,
          $('buildings-container').querySelector('.building-placeholder[data-id="D_Dwelling"]')
        );
      }

      if (n.args.player_id == this.player_id) {
        this.slide(`building-${building.id}`, this.getBuildingContainer(building)).then(() => {
          if (building.type == 'G_OfficeRoom') {
            $(`board-${this.player_id}`).classList.add('office-room');
          }
          this.notifqueue.setSynchronousDuration(10);
        });
      } else {
        this.showBuildingDetails(building, n);
      }
      return null;
    },
  });
});
