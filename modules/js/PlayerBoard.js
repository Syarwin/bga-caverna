define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  const resources = ['wood', 'stone', 'ore', 'ruby', 'grain', 'vegetable', 'food', 'sheep', 'pig', 'cattle', 'donkey', 'dog'];

  return declare('caverna.playerBoard', null, {
    getCell(pos, pId = null) {
      if (pId == null) {
        pId = pos.pId ? pos.pId : this.player_id;
      }

      // Handle fieldCards
      if ((pos.x == -1 && pos.y == -1) || (pos.id && pos.type && pos.type == 'fieldCard' && $(pos.id))) {
        let cardId = pos.id || pos.location;
        let elem = $(cardId).querySelector('.player-card-field-cell');
        dojo.addClass(elem, 'active');
        return elem;
      }

      return document.querySelector(`#board-${pId} .board-cell[data-x='${pos.x}'][data-y='${pos.y}']`);
    },

    /**
     * Player board tpl
     */
    tplPlayerBoard(player) {
      let current = player.id == this.player_id ? 'current' : '';
      if (this.isSpectator && Object.keys(this.gamedatas.players)[0] == player.id) {
        current = 'current';
      }
      let name = player.id == this.player_id ? _('Your board') : player.name;
      let html =
        `
    <div class='player-board-resizable' id='player-board-resizable-${player.id}'>
      <div class="player-board-wrapper ${current}" id="board-wrapper-${player.id}">
        <div class="player-board-holder">
          <div class="animals-counters">
            ` +
        this.tplResourceCounter(player, 'sheep') +
        this.tplResourceCounter(player, 'pig') +
        this.tplResourceCounter(player, 'cattle') +
        this.tplResourceCounter(player, 'donkey') +
        this.tplResourceCounter(player, 'dog') +
        `
          </div>
          <div id="resources-bar-holder-${player.id}" class="resources-bar-holder">
            <div class="player-board-name" style="border-color:#${player.color}; color:#${player.color}">
              ${name}
            </div>
          </div>
          <div class="caverna-player-board" id="board-${player.id}" data-color="${player.color}">
            <div class="player-board-grid">
        `;
      for (let y = 0; y <= 6; y++) {
        for (let x = 0; x <= 10; x++) {
          let type = 'edge';
          if (x % 2 == 1 && y % 2 == 1) type = 'node';
          if (x % 2 == 0 && y % 2 == 0) type = 'virtual';

          let content =
            type == 'node'
              ? `
              <div class="node-background"><div class="empty-node"></div></div>
              <div class="animal-holder resource-holder-update"></div>
              <div class="stable-holder"></div>`
              : '';
          if (type == 'edge') content = '<div class="animal-holder resource-holder-update"></div>';

          html += `<div data-x='${x}' data-y='${y}' class="board-cell cell-${type}">${content}</div>`;
        }
      }
      html += `
            </div>
          </div>
          <div class="cards-wrapper" id="cards-wrapper-${player.id}"></div>
        </div>
      </div>
    </div>
      `;

      return html;
    },

    /**
     * Make cards selectable
     */
    promptPlayerBoardZones(zones, min, max, callback, callbackCheck = null) {
      let selectedZones = [];
      let select = (pos) => {
        dojo.addClass(this.getCell(pos), 'selected');
      };
      let unselect = (pos) => {
        dojo.removeClass(this.getCell(pos), 'selected');
      };

      let onClickZone = (pos) => {
        // Specific case where asked to only point out one zone
        // TODO : might add a "confirm" boolean param to overwrite this behavior when needed
        if (min == 1 && max == null) {
          callback(pos);
          return;
        }

        // Toggle element
        let i = selectedZones.findIndex((p) => p == pos);
        if (i == -1) {
          select(pos);
          selectedZones.push(pos);
        } else {
          unselect(pos);
          selectedZones.splice(i, 1);
        }

        // Add clear button
        dojo.destroy('btnClearZoneSelection');
        dojo.destroy('btnConfirmZoneSelection');
        if (selectedZones.length > 0) {
          this.addSecondaryActionButton('btnClearZoneSelection', _('Clear'), () => {
            selectedZones.forEach(unselect);
            selectedZones = [];
            dojo.destroy('btnClearZoneSelection');
            dojo.destroy('btnConfirmZoneSelection');
          });
        }

        // Add confirm button
        if (
          selectedZones.length >= min &&
          selectedZones.length <= max &&
          (callbackCheck == null || callbackCheck(selectedZones))
        ) {
          this.addPrimaryActionButton('btnConfirmZoneSelection', _('Confirm'), () => {
            callback(selectedZones);
          });
        }
      };

      // Attach event to zones
      zones.forEach((pos) => {
        this.onClick(this.getCell(pos), () => onClickZone(pos));
      });
    },

    /**
     * Flip a meeple (field, room) onto a cell
     */
    flipOnCell(meeple) {
      let target = this.getCell(meeple).querySelector('.node-background').firstChild;
      return this.flipAndReplace(target, this.tplMeeple(meeple));
    },

    /**
     * Adding a field to the board
     */
    notif_plow(n) {
      debug('Notif: adding a field ', n);
      let meeple = n.args.field;
      this.flipOnCell(meeple);
    },

    /**
     * Adding rooms to the board
     */
    notif_construct(n) {
      debug('Notif: adding rooms ', n);
      n.args.rooms.forEach((meeple) => {
        this.flipOnCell(meeple);
        this.getCell(meeple).querySelector('.node-background').classList.add('containsRoom');
      });
    },

    /**
     * Renovating rooms on the board
     */
    notif_renovate(n) {
      debug('Notif: renovating rooms', n);
      n.args.rooms.forEach((meeple) => {
        this.flipAndReplace('meeple-' + meeple.oldId, this.tplMeeple(meeple));
      });
    },

    /**
     * Adding fences to the board
     */
    notif_addFences(n) {
      debug('Notif: adding fences ', n);

      // Update directions of fence
      n.args.fences.forEach((fence, i) => {
        let className = 'fence-' + (fence.x % 2 == 1 ? 'hor' : 'ver');
        dojo.removeClass('meeple-' + fence.id, 'fence-hor fence-ver');
        dojo.addClass('meeple-' + fence.id, className);
      });

      // Slide them
      this.slideResources(n.args.fences, (fence, i) => ({
        target: this.getCell(fence),
        delay: 200 * i,
      }));
    },

    /**
     * Adding stables to the board
     */
    notif_addStables(n) {
      debug('Notif: adding stables', n);
      this.slideResources(n.args.stables, {});
    },
  });
});
