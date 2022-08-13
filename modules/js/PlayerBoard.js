define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  const resources = [
    'wood',
    'stone',
    'ore',
    'ruby',
    'grain',
    'vegetable',
    'food',
    'sheep',
    'pig',
    'cattle',
    'donkey',
    'dog',
  ];

  return declare('caverna.playerBoard', null, {
    getCell(pos, pId = null) {
      if (pId == null) {
        pId = pos.pId ? pos.pId : this.player_id;
      }

      return document.querySelector(`#board-${pId} .board-cell[data-x='${pos.x}'][data-y='${pos.y}']`);
    },

    getAllCells(pId = null) {
      if (pId == null) {
        pId = this.player_id;
      }

      return [...document.querySelectorAll(`#board-${pId} .board-cell`)];
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
      for (let y = -1; y <= 9; y++) {
        for (let x = -1; x <= 13; x++) {
          let type = 'edge';
          if ((x + 2) % 2 == 1 && (y + 2) % 2 == 1) type = 'node';
          if ((x + 2) % 2 == 0 && (y + 2) % 2 == 0) type = 'virtual';

          let content =
            type == 'node'
              ? `
              <div class="animal-holder resource-holder-update"></div>
              <div class="stable-holder"></div>`
              : '';

          html += `<div data-x='${x}' data-y='${y}' class="board-cell cell-${type}">${content}</div>`;
        }
      }
      html += `
            </div>
          </div>
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

    notif_furnish(n) {
      debug('Notif: furnishing ', n);
      // TODO
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

    ///////////////////////////////
    //  _____ _ _
    // |_   _(_) | ___  ___
    //   | | | | |/ _ \/ __|
    //   | | | | |  __/\__ \
    //   |_| |_|_|\___||___/
    //
    ///////////////////////////////

    setupTiles() {
      // This function is refreshUI compatible
      let tileIds = this.gamedatas.tiles.map((tile) => {
        if (!$('tile-' + tile.id)) {
          this.addTile(tile);
        }

        let o = $('tile-' + tile.id);
        let container = this.getTileContainer(tile);
        if (o.parentNode != $(container)) {
          dojo.place(o, container);
        }

        return tile.id;
      });

      document.querySelectorAll('.caverna-tile').forEach((oTile) => {
        if (!tileIds.includes(parseInt(oTile.getAttribute('data-id')))) {
          dojo.destroy(oTile);
        }
      });
    },

    addTile(tile, location = null) {
      if ($('tile-' + tile.id)) return;
      this.place('tplTile', tile, location == null ? this.getTileContainer(tile) : location);
    },

    tplTile(tile) {
      let t = tile.asset.split('_');
      let rotation = t.length == 2 ? t[1] : 0;
      return `<div class="caverna-tile" id="tile-${tile.id}" data-id="${tile.id}" data-tile="${t[0]}" data-rotation="${rotation}"></div>`;
    },

    getTileContainer(tile) {
      return this.getCell(tile);
    },

    /****************
     ** PLACE TILE **
     ****************/
    onEnteringStatePlaceTile(args) {
      const TILES_MAPPING = {
        tileTunnelCavern: ['tunnel', 'cavern'],
        tileCavernCavern: ['cavern', 'cavern'],
        tileMeadowField: ['meadow', 'field'],
        tileMineDeepTunnel: ['deep', 'oreMine'],
        tileRubyMine: ['rubyMine'],
        tileMeadow: ['meadow'],
        tileField: ['field'],
        tileCavern: ['cavern'],
        tileTunnel: ['tunnel'],
      };

      // Clear function
      let selectedTile = null;
      let selectedIndex = null;
      let firstSelectedCell = null;
      let selectedPos = [];
      let clearSelection = () => {
        dojo.query('#tiles-selector .tile-selector-cell').removeClass('selected done');
        dojo.query('.square-selector').forEach((selector) => {
          delete selector.dataset.tile;
          delete selector.dataset.rotation;
        });
        $('subtitle-text').innerHTML = _('Click on a tile square to place it');

        selectedTile = null;
        selectedIndex = null;
        firstSelectedCell = null;
        selectedPos = [];
        updateSelectable();
      };

      // Add subtitle
      $('page-subtitle').innerHTML = `<h4 id='subtitle-text'></h4><div id='tiles-selector'></div>`;

      // Add tiles selectors
      Object.keys(args.zones).forEach((tile) => {
        dojo.place(`<div class='tile-selector' id='tile-selector-${tile}'></div>`, 'tiles-selector');
        TILES_MAPPING[tile].forEach((tileType, i) => {
          let square = dojo.place(
            `<div class='tile-selector-cell' id='tile-selector-${tile}-${i}' data-tile='${tile}-${i}'></div>`,
            `tile-selector-${tile}`,
          );
          this.onClick(square, () => {
            if (square.classList.contains('selected')) return;

            // Clear previous selection if any
            if (selectedTile != null && selectedTile != tile) {
              clearSelection();
            }
            $('subtitle-text').innerHTML = _('Click on your player board to place that square');
            $(`tile-selector-${tile}-${i}`).classList.add('selected');
            selectedTile = tile;
            selectedIndex = i;
            updateSelectable();
          });
        });
      });

      // Add listeners on cells
      this.getAllCells().forEach((cell) => {
        let squareSelector = dojo.place(`<div class='square-selector'></div>`, cell);

        this.onClick(cell, () => {
          if (!cell.classList.contains('selectable')) return false;

          selectedPos[selectedIndex] = {
            x: cell.dataset.x,
            y: cell.dataset.y,
          };
          if (firstSelectedCell == null) {
            firstSelectedCell = cell;
          }
          squareSelector.dataset.tile = `${selectedTile}-${selectedIndex}`;
          $(`tile-selector-${selectedTile}-${selectedIndex}`).classList.add('done');

          // If tile is only 1 square or the two squares are placed => confirm button
          if (TILES_MAPPING[selectedTile].length == 1 || (selectedPos[0] && selectedPos[1])) {
            selectedIndex = null;
            $('subtitle-text').innerHTML = _('Confirm your placement');
            this.addPrimaryActionButton(
              'btnConfirmPlace',
              _('Confirm'),
              () => this.takeAtomicAction('actPlaceTile', [selectedTile, selectedPos]),
              'subtitle-text',
            );
          }
          // Otherwise, auto select other square
          else {
            selectedIndex = 1 - selectedIndex;
            $(`tile-selector-${selectedTile}-${selectedIndex}`).classList.add('selected');
          }
          updateSelectable();
        });

        this.connect(cell, 'mouseenter', () => {
          if (!cell.classList.contains('selectable')) return false;

          squareSelector.dataset.tile = `${selectedTile}-${selectedIndex}`;
          if (firstSelectedCell != null) {
            let rotation = 0;
            let dx = parseInt(firstSelectedCell.dataset.x) - parseInt(cell.dataset.x);
            let dy = parseInt(firstSelectedCell.dataset.y) - parseInt(cell.dataset.y);
            // Left/right
            if (dy == 0) {
              if ((dx > 0 && selectedIndex == 1) || (dx < 0 && selectedIndex == 0)) {
                rotation = 2;
              }
            } else {
              rotation = dy > 0 ? 1 : 3;
              if (selectedIndex == 0) {
                rotation = 4 - rotation;
              }
            }

            firstSelectedCell.querySelector('.square-selector').dataset.rotation = rotation;
            squareSelector.dataset.rotation = rotation;
          }
        });

        this.connect(cell, 'mouseleave', () => {
          if (!cell.classList.contains('selectable')) return false;

          delete squareSelector.dataset.tile;
          if (firstSelectedCell != null) {
            delete firstSelectedCell.querySelector('.square-selector').dataset.rotation;
            delete squareSelector.dataset.rotation;
          }
        });
      });

      // Class update for cells
      let updateSelectable = () => {
        this.getAllCells().forEach((cell) => {
          let selectable = selectedTile != null && selectedIndex != null;
          if (selectable) {
            selectable = args.zones[selectedTile].reduce((carry, zone) => {
              if (carry) return true;
              if (selectedPos[0] !== undefined && (selectedPos[0].x != zone.pos1.x || selectedPos[0].y != zone.pos1.y))
                return false;
              if (selectedPos[1] !== undefined && (selectedPos[1].x != zone.pos2.x || selectedPos[1].y != zone.pos2.y))
                return false;

              let pos = selectedIndex == 0 ? zone.pos1 : zone.pos2;
              return pos.x == cell.dataset.x && pos.y == cell.dataset.y;
            }, false);
          }
          cell.classList.toggle('selectable', selectable);
        });

        if (selectedTile != null) {
          this.addSecondaryActionButton('btnCancelPlace', _('Cancel'), () => clearSelection(), 'subtitle-text');
        }
      };

      clearSelection();
      updateSelectable();
    },

    notif_placeTile(n) {
      debug('Notif: placing a tile', n);
      if (n.args.player_id == this.player_id) {
        dojo.query('.square-selector').forEach(dojo.destroy);
        dojo.empty('page-subtitle');
      }

      n.args.squares.forEach((square) => {
        this.addTile(square);
      });
    },
  });
});
