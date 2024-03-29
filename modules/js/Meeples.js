define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  const MEEPLES = [
    'SCORE',
    'GOLD',
    'WOOD',
    'STONE',
    'ORE',
    'RUBY',
    'FOOD',
    'GRAIN',
    'VEGETABLE',
    'SHEEP',
    'PIG',
    'CATTLE',
    'DONKEY',
    'DOG',
    'BEGGING',

    'DWARF',
    'STABLE',
    'BARN',

    'ARROW',
    'ARROW-1X',
    'ARROW-2X',
    'FIRST',
    'REORGANIZE',
    'PASTURE',
    'EMPTY',
    'CAVERN',
    'FIELD',
    'FURNISH',
    'LARGE_PASTURE',
    'SMALL_PASTURE',
    'MEADOW',
    'SOW',
    'TUNNEL',
    'MINES',
    'BUILDING',
  ];
  const PERSONAL_RESOURCES = ['dwarf', 'stable'];

  return declare('caverna.meeples', null, {
    setupMeeples() {
      // This function is refreshUI compatible
      let meepleIds = this.gamedatas.meeples.map((meeple) => {
        if (!$('meeple-' + meeple.id)) {
          this.addMeeple(meeple);
        }

        let o = $('meeple-' + meeple.id);
        let container = this.getMeepleContainer(meeple);
        if (o.parentNode != $(container)) {
          dojo.place(o, container);
        }
        if (meeple.type == 'weapon') {
          o.dataset.force = meeple.state;
        }

        return meeple.id;
      });
      document.querySelectorAll('.caverna-meeple[id^="meeple-"]').forEach((oMeeple) => {
        if (!meepleIds.includes(parseInt(oMeeple.getAttribute('data-id')))) {
          dojo.destroy(oMeeple);
        }
      });
      document.querySelectorAll('.node-background').forEach((node) => {
        if (node.childNodes.length == 0) {
          dojo.place('<div class="empty-node"></div>', node);
          node.classList.remove('containsRoom');
        }
      });
      this.updateResourcesHolders(false, false);
    },

    updateResourcesHolders(preAnimation, anim = true) {
      dojo
        .query('.resource-holder-update')
        .forEach((container) => dojo.attr(container, 'data-n', container.childNodes.length));
      this.updatePlayersCounters(anim);
      this.updateDropZonesStatus(preAnimation);
    },

    localUpdateResourcesHolders(meeple, leaving) {
      let parent = meeple.parentNode;
      if (parent.classList.contains('resource-holder-update')) {
        let n = parseInt(parent.getAttribute('data-n') || 0);
        parent.setAttribute('data-n', n + (leaving ? -1 : 1));
      } else if (parent.classList.contains('reserve')) {
        let t = parent.id.split('_');
        if (PERSONAL_RESOURCES.includes(t[2])) {
          leaving = !leaving;
        }

        let n = parseInt(parent.parentNode.getAttribute('data-n') || 0);
        n += leaving ? -1 : 1;
        this._playerCounters[t[1]][t[2]].toValue(n);
        parent.parentNode.setAttribute('data-n', n);
      }

      let type = meeple.getAttribute('data-type');
      this.localUpdateResourcesHoldersType(type);
    },

    localUpdateResourcesHoldersType(type) {
      if (['dog', 'sheep', 'pig', 'cattle', 'donkey'].includes(type)) {
        this.updateAnimalsPlayerCounters(type);
        this.updateDropZonesStatus(true);
      } else if (type == 'dwarf') {
        this.updateDwarfsPlayerCounters();
      }
    },

    addMeeple(meeple, location = null) {
      if ($('meeple-' + meeple.id)) return;
      this.place('tplMeeple', meeple, location == null ? this.getMeepleContainer(meeple) : location);

      if (meeple.type == 'harvest_normal') {
        this.addCustomTooltip(`meeple-${meeple.id}`, _('A normal harvest will take place at the end of this round.'));
      } else if (meeple.type == 'harvest_none') {
        this.addCustomTooltip(`meeple-${meeple.id}`, _('This round will end without any harvest.'));
      } else if (meeple.type == 'harvest_1food') {
        this.addCustomTooltip(
          `meeple-${meeple.id}`,
          _(
            'At the end of this round, there is no harvest but instead you have to pay 1 Food per Dwarf in your cave (even for offspring Dwarfs). There is no Field or Breeding phase at this time.'
          )
        );
      } else if (meeple.type == 'harvest_grey') {
        this.addCustomTooltip(
          `meeple-${meeple.id}`,
          _('At the end of this round, there will be a harvest unless the hidden marker shows a red question mark.')
        );
      } else if (meeple.type == 'harvest_choice') {
        this.addCustomTooltip(
          `meeple-${meeple.id}`,
          _(
            'At the end of this round, each player will decide individually whether they want to play the Field phase or the Breeding phase of the Harvest time at the end of the round. (You cannot play both these phases, but you must still play the Feeding phase. '
          )
        );
      }
    },

    tplMeeple(meeple) {
      let color = meeple.pId == 0 ? meeple.pId : this.getPlayerColor(meeple.pId);
      let className = '';
      if (meeple.type == 'dwarf') {
        className = meeple.state == 1 ? 'child' : '';
      }
      if (meeple.type == 'field' && meeple.location != 'board') {
        meeple.type = 'field_icon';
      }

      if (meeple.type == 'weapon') {
        return `<div class="caverna-meeple meeple-weapon" id="meeple-${meeple.id}" data-id="${meeple.id}" data-force="${meeple.state}"></div>`;
      }
      return `<div class="caverna-meeple meeple-${meeple.type} ${className}" id="meeple-${meeple.id}" data-id="${meeple.id}" data-color="${color}" data-type="${meeple.type}"></div>`;
    },

    getMeepleContainer(meeple) {
      if (meeple.type == 'weapon') {
        return $(`meeple-${meeple.location}`);
      } else if (meeple.location == 'board') {
        let container = this.getCell(meeple);

        if (['vegetable', 'grain'].includes(meeple.type)) {
          let field = container.querySelector('.caverna-tile');
          container = this.getSeedContainer({ id: field.getAttribute('data-id') });
        }

        if (['sheep', 'pig', 'cattle', 'donkey', 'dog'].includes(meeple.type)) {
          container = container.querySelector('.animal-holder');
        }

        if (['stable'].includes(meeple.type)) {
          container = container.querySelector('.stable-holder');
        }

        if (['field', 'roomWood', 'roomClay', 'roomStone'].includes(meeple.type)) {
          container = container.querySelector('.node-background');
          dojo.empty(container);
          dojo.addClass(container, 'containsRoom');
        }

        return container;
      } else if (meeple.location == 'reserve') {
        if (meeple.type == 'dwarf') {
          return $(`board_resource_${meeple.pId}_dwarf`).querySelector('.dwarf-in-reserve');
        }
        let reserve = $(`reserve_${meeple.pId}_${meeple.type}`);
        if (reserve == null) {
          reserve = 'reserve-' + meeple.pId;
        }
        return reserve;
      } else if (meeple.type.substring(0, 7) == 'harvest') {
        if (meeple.location == 'harvest') return $('current-harvest');
        if (meeple.location == 'history')
          return $('player_config').querySelector(`.harvest-indicator[data-type="${meeple.type}"]`);
        return $(meeple.location).querySelector('.harvest-token-container');
      } else if (meeple.location.substr(0, 4) == 'turn') {
        return $(meeple.location).querySelector('[data-pid="' + meeple.pId + '"]');
      } else if ($(meeple.location)) {
        let holder = meeple.type == 'dwarf' ? 'dwarf' : 'resource';
        if (meeple.type == 'score') {
          return document.querySelector('#' + meeple.location + ' .card-bonus-vp-counter');
        }

        return document.querySelector('#' + meeple.location + ' .' + holder + '-holder');
      }

      console.error('Trying to get container of a meeple', meeple);
      return 'game_play_area';
    },

    /**
     * Placing a dwarf on an action card
     */
    notif_placeDwarf(n) {
      debug('Notif: place dwarf ', n);
      let meeple = {
        id: n.args.dwarf,
        location: n.args.card.id,
        type: 'dwarf',
      };
      this.slideResources([meeple], {
        duration: 1000,
      });
    },

    /**
     * Wrap the sliding animations with a call to updateResourcesHolders() before and after the sliding is done
     */
    slideResources(meeples, configFn, syncNotif = true, updateHoldersAtEachMeeples = true) {
      let fakeId = -1; // Used for virtual meeple that will get destroyed after animation (eg SCORE)
      let promises = meeples.map((resource, i) => {
        // Get config for this slide
        let config = typeof configFn === 'function' ? configFn(resource, i) : Object.assign({}, configFn);
        if (resource.destroy) {
          resource.id = fakeId--;
          config.destroy = true;
        }

        // Default delay if not specified
        let delay = config.delay ? config.delay : 100 * i;
        config.delay = 0;
        // Use meepleContainer if target not specified
        let target = config.target ? config.target : this.getMeepleContainer(resource);

        // Slide it
        let slideIt = () => {
          // Create meeple if needed
          if (!$('meeple-' + resource.id)) {
            this.addMeeple(resource);
          } else {
            this.localUpdateResourcesHolders($('meeple-' + resource.id), true);
          }

          // Slide it
          return this.slide('meeple-' + resource.id, target, config);
        };

        // Update locally
        let updateCounters = () => {
          if (updateHoldersAtEachMeeples) {
            if ($('meeple-' + resource.id)) {
              this.localUpdateResourcesHolders($('meeple-' + resource.id), false);
            } else {
              this.localUpdateResourcesHoldersType(resource.type);
            }
          }
        };

        if (this.isFastMode()) {
          slideIt();
          updateCounters();
          return null;
        } else {
          return this.wait(delay - 10)
            .then(slideIt)
            .then(updateCounters);
        }
      });

      // Update counters of receiving holders once all the promises are resolved
      let finalCounterUpdate = () => {
        if (!updateHoldersAtEachMeeples) {
          this.updateResourcesHolders(false);
        }

        if (syncNotif) {
          this.notifqueue.setSynchronousDuration(this.isFastMode() ? 0 : 10);
        }
      };

      if (this.isFastMode()) {
        finalCounterUpdate();
        return;
      } else
        return Promise.all(promises)
          .then(() => this.wait(10))
          .then(finalCounterUpdate);
    },

    /**
     * Accumulation on action cards
     */
    notif_accumulation(n) {
      debug('Notif: accumulation', n);
      this.slideResources(n.args.resources, (meeple) => ({
        from: document.querySelector('#' + meeple.location + ' .action-desc'),
      }));
    },

    /**
     * Collect resources from a card
     */
    notif_collectResources(n) {
      debug('Notif: collecting resoures', n);
      this.slideResources(n.args.resources, {});
    },

    /**
     * Gain resources (create them from the reserve)
     */
    notif_gainResources(n) {
      debug('Notif: gain resoures', n);
      this.slideResources(n.args.resources, {
        from: n.args.cardId ? n.args.cardId : 'page-title',
      });
    },

    /**
     * Pay resources for an action
     */
    notif_payResources(n) {
      debug('Notif: paying resoures', n);
      this.slideResources(n.args.resources, (meeple) => ({
        target: 'page-title',
        destroy: true,
      }));
    },

    /**
     * Silently kill/remove a meeple
     */
    notif_silentKill(n) {
      debug('Notif: silenKill', n);
      this.slideResources(n.args.resources, (meeple) => ({
        //target: 'pagemaintitletext',
        duration: 10,
        destroy: true,
      }));
    },

    notif_silentDestroy(n) {
      debug('Notif: silent destroy', n);
      n.args.resources.forEach((meeple) => dojo.destroy('meeple-' + meeple.id));
    },

    notif_revealHarvestToken(n) {
      debug('Notif: reveal harvest token', n);
      let token = n.args.token;
      let existing = $(`meeple-${token.id}`);
      existing.id = 'flipping-harvest-token';
      this.addMeeple(token);
      this.flipAndReplace(existing, $(`meeple-${token.id}`));
    },

    /**
     * A player picked the firstplayer token
     */
    notif_firstPlayer(n) {
      debug('Notif: moving first player token', n);
      this.slide('meeple-' + n.args.meepleId, 'reserve_' + n.args.player_id + '_firstPlayer', {});
    },

    /**
     * All dwarfs return home
     */
    notif_returnHome(n) {
      debug('Notif: returning home', n);
      this.slideResources(n.args.dwarfs, {
        delay: 0,
        duration: 1200,
      });
    },

    /**
     * Equip a weapon on a dwarf
     */
    notif_equipWeapon(n) {
      debug('Notif: equip a weapon', n);
      if (this.isFastMode()) {
        this.slideResources([n.args.weapon], {
          from: 'page-title',
        });
        this.updateDwarfsPlayerCounters();
        return;
      }

      this.slideResources([n.args.weapon], {
        from: 'page-title',
      }).then(() => this.updateDwarfsPlayerCounters());
    },

    /**
     * Equip a weapon on one or multiple dwarf(ves)
     */
    notif_upgradeWeapon(n) {
      debug('Notif : upgrading weapons', n);
      n.args.dwarfs.forEach((dwarf) => {
        $(`meeple-${dwarf.weaponId}`).dataset.force = dwarf.weapon;
      });
      this.updateDwarfsPlayerCounters();
    },

    /**
     * An extra dwarf makes popin
     */
    notif_growFamily(n) {
      debug('Notif: grow family', n);
      this.slideResources([n.args.meeple], {});
      dojo.addClass('meeple-' + n.args.meeple.id, 'child');
      return null;
    },

    /**
     * Children becomes adult
     */
    notif_growChildren(n) {
      debug('Notif: growing children', n);
      n.args.ids.forEach((mId) => dojo.removeClass('meeple-' + mId, 'child'));
      this.updateDwarfsPlayerCounters();
    },

    /**
     * Some meeples are placed on future action cards
     */
    notif_placeMeeplesForFuture(n) {
      debug('Notif: placing meeples for future', n);
      this.slideResources(n.args.meeples, {
        from: 'page-title',
      });
    },

    /**
     * A player harvest crops
     */
    notif_harvestCrop(n) {
      debug('Notif: harvesting crops', n);
      this.slideResources(n.args.resources, {});
    },

    /**
     * Replace some expressions by corresponding html formating
     */
    formatStringMeeples(str) {
      // This text icon are also board component, so we add the prefix _icon to distinguish them
      let conflictingNames = ['DWARF'];

      let jstpl_meeple = `
      <div class="meeple-container">
        <div class="caverna-meeple meeple-\${type}">
        </div>
      </div>
      `;
      MEEPLES.forEach((name) => {
        let newName = name.toLowerCase() + (conflictingNames.includes(name) ? '_icon' : '');
        str = str.replace(new RegExp('<' + name + '>', 'g'), this.format_string(jstpl_meeple, { type: newName }));
        str = str.replace(/\[([^\]]+)\]/gi, '<span class="text">$1</span>'); // Replace [my text] by <span clas="text">my text</span>
      });
      str = str.replace(/\{\{([^\}]+)\}\}/gi, '<div class="text-wrapper">$1</div>'); // Replace {{my wrapped text}} by <div clas="text-wrapper">my wrapped text</div>
      str = str.replace(
        '<EXCHANGE>',
        `<svg class="exchange-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--! Font Awesome Pro 6.2.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2022 Fonticons, Inc. --><path d="M422.6 278.6c-12.5 12.5-32.8 12.5-45.3 0s-12.5-32.8 0-45.3L434.7 176H64c-17.7 0-32-14.3-32-32s14.3-32 32-32H434.7L377.4 54.6c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0l112 112c12.5 12.5 12.5 32.8 0 45.3l-112 112zm-269.3 224l-112-112c-12.5-12.5-12.5-32.8 0-45.3l112-112c12.5-12.5 32.8-12.5 45.3 0s12.5 32.8 0 45.3L141.3 336H512c17.7 0 32 14.3 32 32s-14.3 32-32 32H141.3l57.4 57.4c12.5 12.5 12.5 32.8 0 45.3s-32.8 12.5-45.3 0z"/></svg>`
      );
      return str;
    },

    /**
     * Return a string corresponding to an array of resources
     * [
     *   resourceType => amount,
     *   ...
     * ]
     */
    formatResourceArray(resources, formatMeeples = true) {
      let formated = [];
      Object.keys(resources).forEach((type) => {
        if (!MEEPLES.includes(type.toUpperCase())) return;

        let v = resources[type];
        formated.push((v > 1 ? v : '') + '<' + type.toUpperCase() + '>');
      });
      let desc = formated.join(',');
      return formatMeeples ? this.formatStringMeeples(desc) : desc;
    },

    /**
     * Format log strings
     *  @Override
     */
    format_string_recursive(log, args) {
      try {
        if (log && args && !args.processed) {
          args.processed = true;

          // Representation of the class of a card
          if (args.resources_desc !== undefined) {
            args.resources_desc = this.formatStringMeeples(args.resources_desc);
          }
          if (args.resources2_desc !== undefined) {
            args.resources2_desc = this.formatStringMeeples(args.resources2_desc);
          }

          // Replace __str__ by italic wrapper
          log = log.replace(/__([^_]+)__/g, '<span class="action-card-name-reference">$1</span>');
        }
      } catch (e) {
        console.error(log, args, 'Exception thrown', e.stack);
      }

      return this.inherited(arguments);
    },
  });
});
