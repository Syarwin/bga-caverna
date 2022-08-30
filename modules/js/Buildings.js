define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
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
          'buildings-container',
        );

        buildingsBoards[type].forEach((buildingId, i) => {
          dojo.place(
            `<div class="building-placeholder" data-id="${buildingId}"></div>`,
            board.querySelector(i < 6 ? '.building-board-left' : '.building-board-right'),
          );
        });

        dojo.place(
          `<div id='show-building-board-${type}' class='building-board-button' data-id='${type}'><i class="fa fa-times" aria-hidden="true"></i></div>`,
          'floating-building-buttons',
        );
        $(`show-building-board-${type}`).addEventListener('click', (evt) => this.goToBuildingBoard(type, evt));
      });

      // Create an overlay for building animations
      dojo.place("<div id='building-overlay'></div>", 'ebd-body');
      dojo.connect($('building-overlay'), 'click', () => this.zoomOffBuilding());

      //this.setupMajorsImprovements();
      this.updateBuildings();
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
      let buildingIds = this.gamedatas.buildings.map((building) => {
        this.loadSaveBuilding(building);
        // Create the building if needed
        if (!$(`building-${building.id}`)) {
          this.addBuilding(building);
        }
        // Move the building if not correct container
        let o = $(`building-${building.id}`);
        let container = this.getBuildingContainer(building);
        if (o.parentNode != $(container)) {
          dojo.place(o, container);
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
     * Create the modal that holds the major improvements
     */
    setupBuildingsModal() {
      this._buildingsDialog = new customgame.modal('showBuildings', {
        class: 'caverna_popin',
        closeIcon: 'fa-times',
        //        openAnimation: true,
        //        openAnimationTarget: 'majors-button',
        contents:
          `
          <div id="majors-container">
            ` +
          MAJORS.map((id) => `<div class="major-holder" id="Major_${id}_holder"></div>`).join('') +
          `
          </div>
        `,
        closeAction: 'hide',
        statusElt: 'majors-button',
        verticalAlign: 'flex-start',
        scale: 0.8,
        breakpoint: 1200,
      });

      this.addCustomTooltip('buildings-button', _('Display available major improvements'));
      this.onClick('buildings-button', () => this.openMajorsModal(), false);
    },

    /**
     * Open the major improvement modal
     */
    openMajorsModal() {
      this._majorsDialog.show();
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
        if (this._buildingStorage[building.id] === undefined) {
          console.error('Missing building informations :', building);
          return;
        }

        ['text', 'extraVp', 'name', 'tooltip', 'type', 'vp'].forEach(
          (info) => (building[info] = this._buildingStorage[building.id][info]),
        );
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
      let oBuilding = this.place('tplBuilding', building, container);
      this.addCustomTooltip(`building-${building.id}`, this.tplBuildingTooltip(building));
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

    /**
     * Prompt current player to pick a building
     */
    promptBuilding(types, buildings, callback) {
      if (this.isFastMode()) return;

      // Majors
      if (types.includes('major')) {
        dojo.query('#majors-container .player-building').addClass('unselectable');
        this.addPrimaryActionButton('btnOpenMajorModal', _('Show major improvements'), () => this.openMajorsModal());

        if (types.length == 1) {
          // If only major are prompted, auto open modal
          this.openMajorsModal();
        }
      }

      // Add event listener
      buildings.forEach((buildingId) => this.onClick(buildingId, () => callback(buildingId)));
    },

    computeSlidingAnimationFrom(building, newContainer) {
      let from = 'hand-button';
      if (!$(building.id)) {
        this.addBuilding(building, newContainer);
        from = 'overall_player_board_' + building.pId;
      } else {
        dojo.place(building.id, newContainer);
        if (building.type == 'major') {
          from = this._majorsDialog.isDisplayed() ? building.id + '_holder' : 'majors-button';
        } else {
          from = this._handDialog.isDisplayed() || this.prefs[HAND_CARDS].value != 0 ? 'hand-container' : 'hand-button';
        }
      }

      this.updatePlayerBoardDimensions();
      return from;
    },

    /**
     * Notification when someone bought a building
     */
    notif_furnish(n) {
      debug('Notif: buying a building', n);
      let building = n.args.building;
      return; // TODO

      let duration = 700;
      let waitingTime = 80000 / this._buildingAnimationSpeed;

      // Create the building if needed, and compute initial location of sliding event
      let exists = $(building.id);
      let from = this.computeSlidingAnimationFrom(building, 'buildings-wrapper-' + building.pId);

      // Zoom on it, then zoom off
      if (this.isFastMode()) {
        this.notifqueue.setSynchronousDuration(0);
      } else {
        this.zoomOnBuilding(building.id, { from, duration })
          .then(() => this.wait(waitingTime))
          .then(() => this.zoomOffBuilding({ duration }))
          .then(() => this.notifqueue.setSynchronousDuration(10));
      }

      // Close major modal if open
      if (this._majorsDialog.isDisplayed()) {
        this._majorsDialog.hide();
      }

      return null;
    },

    /**
     * Zoom on a building
     */
    zoomOnBuilding(buildingId, config = {}) {
      this._zoomedBuilding = buildingId;
      let originalBuilding = $(buildingId);
      let animatedBuilding = dojo.clone(originalBuilding);
      let scale =
        (parseFloat(this.getScale(originalBuilding.querySelector('.player-building-resizable'))) * 100) /
        parseInt(this._buildingScale);

      dojo.addClass(originalBuilding, 'phantom'); // Make the original building invisible
      dojo.attr(animatedBuilding, 'id', buildingId + '_animated'); // Add a prefix to avoid duplicate id
      dojo.style(animatedBuilding, 'transform', `scale(${scale})`);
      dojo.empty('building-overlay');
      dojo.place(animatedBuilding, 'building-overlay');

      // Start animation
      config.from = config.from || originalBuilding;
      let anim = this.slide(animatedBuilding, 'building-overlay', config);
      dojo.addClass('building-overlay', 'active');
      dojo.style(animatedBuilding, 'transform', `scale(${100 / parseInt(this._buildingScale)})`);
      dojo.removeClass(animatedBuilding, 'mini');
      return anim;
    },

    /**
     * Zoom off a building
     */
    zoomOffBuilding(config = {}) {
      if (this._zoomedBuilding == null) return;

      let buildingId = this._zoomedBuilding;
      let originalBuilding = $(buildingId);
      let animatedBuilding = $(buildingId + '_animated');
      let scale =
        (parseFloat(this.getScale(originalBuilding.querySelector('.player-building-resizable'))) * 100) /
        parseInt(this._buildingScale);

      config.destroy = true;
      let anim = this.slide(animatedBuilding, buildingId, config).then(() =>
        dojo.removeClass(originalBuilding, 'phantom'),
      );
      dojo.removeClass('building-overlay', 'active');
      dojo.style(animatedBuilding, 'transform', `scale(${scale})`);
      dojo.addClass(animatedBuilding, 'mini');
      this._zoomedBuilding = null;
      return anim;
    },
  });
});
