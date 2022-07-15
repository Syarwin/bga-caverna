define(['dojo', 'dojo/_base/declare'], (dojo, declare) => {
  const MAJOR = 'major';
  const PLAYER_BOARDS = 107;
  const HAND_CARDS = 108;

  return declare('caverna.cards', null, {
    setupBuildings() {
      // Create an overlay for card animations
      dojo.place("<div id='card-overlay'></div>", 'ebd-body');
      dojo.connect($('card-overlay'), 'click', () => this.zoomOffCard());

      this.setupMajorsImprovements();
      this.setupHandModal();
      this.updateBuildings();
      this.updateHandContainer();

      if (!this.isSpectator) {
        this.updateHandCards();

        //        ['hand-container-minor', 'hand-container-occupation'].forEach((container) => {
        ['hand-container'].forEach((container) => {
          sortable('#' + container, {
            handle: '.card-icon',
            forcePlaceholderSize: true,
            placeholderClass: 'card-placeholder',
          });

          $(container).addEventListener('sortstart', (e) => {
            let cardId = e.detail.item.getAttribute('data-id');
            let tooltip = this.tooltips[cardId];
            tooltip.close();
            if (tooltip.showTimeout != null) clearTimeout(tooltip.showTimeout);
            this._dragndropMode = true;
          });

          $(container).addEventListener('sortstop', (e) => {
            this._dragndropMode = false;
          });

          $(container).addEventListener('sortupdate', (e) => {
            let ids = e.detail.destination.items.map((element) => element.getAttribute('data-id'));
            this.takeAction('actOrderCards', { cardIds: JSON.stringify(ids), lock: false }, false);
          });
        });
      }
    },

    // Place Hand container at the correct spot
    updateHandContainer() {
      let isDraft = this._isDraft;
      dojo.toggleClass('draft-wrapper', 'active', isDraft);
      dojo.setStyle('hand-button', 'display', isDraft || this.gamedatas.isBeginner ? 'none' : 'block');

      let container = isDraft ? 'draft-wrapper' : 'popin_showHand_contents';
      if (!isDraft && this.prefs[HAND_CARDS].value != 0) {
        if (this.prefs[HAND_CARDS].value == 2) {
          container = 'alternative-hand-wrapper';
        } else {
          container = this.prefs[PLAYER_BOARDS].value == 1 ? 'player-boards-left-column' : 'player-boards-separator';
        }

        dojo.setStyle('hand-button', 'display', 'none');
      }
      dojo.place('hand-container', container);

      if (!isDraft && this.prefs[HAND_CARDS].value != 0) {
        this.onScreenWidthChange(); // Set min height for left column cards
      }
    },

    updateBuildings() {
      // This function is refreshUI compatible
      let cardIds = this.gamedatas.playerCards.map((card) => {
        this.loadSaveCard(card);
        // Create the card if needed
        if (!$(card.id)) {
          this.addCard(card);
        }
        // Move the card if not correct container
        let o = $(card.id);
        let container = this.getCardContainer(card);
        if (o.parentNode != $(container)) {
          dojo.place(o, container);
          dojo.toggleClass(o, 'mini', card.mini || card.location == 'inPlay');
        }

        return card.id;
      });

      // All the cards not in specified list must be destroyed
      document.querySelectorAll('.player-card').forEach((oCard) => {
        if (!cardIds.includes(oCard.getAttribute('data-id'))) {
          dojo.destroy(oCard);
        }
      });

      this.updatePlayerBoardDimensions();
    },

    updateHandCards() {
      let player = this.gamedatas.players[this.player_id];
      let cards = player.hand ? player.hand : [];
      dojo.empty('hand-container');
      cards.sort((a, b) => a.state - b.state);
      cards.forEach((card) => this.addCard(card));
    },

    /**
     * Create the modal that holds the major improvements
     */
    setupMajorsImprovements() {
      const MAJORS = [
        'Fireplace1',
        'Fireplace2',
        'CookingHearth1',
        'CookingHearth2',
        'Well',
        'ClayOven',
        'StoneOven',
        'Joinery',
        'Pottery',
        'Basket',
      ];

      this._majorsDialog = new customgame.modal('showMajors', {
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

      this.addCustomTooltip('majors-button', _('Display available major improvements'));
      this.onClick('majors-button', () => this.openMajorsModal(), false);
    },

    /**
     * Open the major improvement modal
     */
    openMajorsModal() {
      this._majorsDialog.show();
    },

    /**
     * Create the modal that holds the cards in hand
     */
    setupHandModal() {
      this._handDialog = new customgame.modal('showHand', {
        class: 'caverna_popin',
        closeIcon: 'fa-times',
        contents: "<div id='hand-container'></div>",
        //          "<div id='hand-container'><div id='hand-container-occupation'></div><div id='hand-container-minor'></div></div>",
        closeAction: 'hide',
        statusElt: 'hand-button',
        verticalAlign: 'flex-start',
        scale: 0.8,
        breakpoint: 1200,
      });

      this.addCustomTooltip('hand-button', _('Display cards in hand'));
      this.onClick('hand-button', () => this.openHandModal(), false);
    },

    /**
     * Open the hand modal
     */
    openHandModal() {
      this._handDialog.show();
    },

    /**
     * Smarter buffering of card details (to not resend them over notification again)
     */
    loadSaveCard(card) {
      if (card.desc) {
        // Card contains all info => save it in cardStorage
        this._cardStorage[card.id] = card;
      } else {
        // Card is missing information : load it from cardStorage
        if (this._cardStorage[card.id] === undefined) {
          console.error('Missing card informations :', card);
          return;
        }

        [
          'actionCard',
          'category',
          'costText',
          'costs',
          'deck',
          'desc',
          'extraVp',
          'fee',
          'field',
          'holder',
          'animalHolder',
          'name',
          'numbering',
          'passing',
          'players',
          'prerequisite',
          'tooltip',
          'type',
          'vp',
        ].forEach((info) => (card[info] = this._cardStorage[card.id][info]));
      }
    },

    /**
     * Add a card to its default location
     */
    addCard(card, container = null) {
      this.loadSaveCard(card);
      card.description = this.formatCardDesc(card.desc);
      card.mini = card.mini ? card.mini : card.location == 'inPlay';

      if (container == null) {
        container = this.getCardContainer(card);
      }
      let oCard = this.place('tplPlayerCard', card, container);
      this.addCustomTooltip(card.id, this.tplPlayerCard(card, true));
      if (card.animalHolder) {
        setTimeout(() => {
          let animalsWrapper = oCard.querySelector('.animals-control');
          dojo.connect(animalsWrapper, 'mouseenter', (evt) => {
            evt.stopPropagation();
          });
        }, 1);
      }
      if (card.mini) {
        oCard.querySelector('.player-card-zoom').addEventListener('click', () => {
          this.zoomOnCard(card.id);
        });
      }

      if (card.pId) {
        this.updatePlayerBoardDimensions(card.pId);
      }
    },

    refreshCardTooltipBonusVp(cardId, newValue) {
      let content = this.tooltips[cardId].label;
      content = content.replace(
        /<div class='card-bonus-vp-counter'>[0-9]*<\/div>/g,
        `<div class='card-bonus-vp-counter'>${newValue}</div>`,
      );
      this.tooltips[cardId].label = content;
    },

    getCardContainer(card) {
      if (card.location == 'inPlay') {
        // Played card => in front of player
        return 'cards-wrapper-' + card.pId;
      } else if (card.type == MAJOR && card.location == 'board') {
        // Available major => in the major modal
        return card.id + '_holder';
      } else if (card.location == 'hand' || card.location == 'selection') {
        // Card in hand => in the 'hand' modal
        return 'hand-container';
        //        return 'hand-container-' + card.type;
      } else if (card.location == 'draft') {
        return 'draft-container';
      }

      console.error('Trying to get container of a card', card);
      return 'game_play_area';
    },

    /**
     * Template for all "player" cards (Improvements and Occupations)
     */
    tplPlayerCard(card, tooltip = false) {
      let formatStr = (s) => _(s).replace(/__([^_]+)__/g, '<span class="action-card-name-reference">$1</span>');

      let costText = card.costText == '' ? '' : formatStr(card.costText);
      let prerequisite = card.prerequisite == '' ? '' : formatStr(card.prerequisite);

      let subHolders = '';
      if (card.id == 'D75_WoodField' && !tooltip) {
        subHolders = '<div class="subholder" data-x="0"></div><div class="subholder" data-x="-1"></div>';
      }

      let uid = (tooltip ? 'tooltip_' : '') + card.id;
      return (
        `<div id="${uid}" data-id='${card.id}' data-numbering='${card.numbering}'
          class='player-card ${card.type} ${card.mini && !tooltip ? 'mini' : ''} ${
          card.location == 'selection' ? 'pending' : ''
        } ${card.animalHolder ? 'animalHolder' : ''}'
          data-cook='${card.cook}' data-bread='${card.bread}'
          data-state='${card.state}'>
          <div class='player-card-resizable'>
            <div class='player-card-inner'>
              <div class='card-frame'></div>
              ` +
        (card.passing === undefined || card.passing === true
          ? ''
          : `<div class='card-frame-left-leaves'></div><div class='card-frame-right-leaves'></div>`) +
        `
              <div class='card-icon'></div>
              <div class='card-title'>
                ${_(card.name)}
              </div>
              <div class='card-numbering'>${card.numbering}</div>
              <div class='card-bonus-vp-counter'>${card.bonusVp}</div>
              ` +
        (card.players == undefined ? '' : `<div class='card-players' data-n="${card.players}"></div>`) +
        (card.deck == undefined ? '' : `<div class='card-deck' data-deck="${card.deck}"></div>`) +
        (card.vp != 0 ? `<div class='card-score' data-score="${card.vp}">${card.vp}</div>` : '') +
        (card.extraVp ? `<div class='card-extra-score'></div>` : '') +
        `
              <div class="card-category" data-category="${card.category}"></div>
              <div class="card-cost">
                ` +
        (costText == '' ? '' : `<div class="card-cost-text">${costText}</div>`) +
        `
                ${this.formatCardCost(card)}
              </div>` +
        (prerequisite == ''
          ? ''
          : `<div class="card-prerequisite"><div class="prerequisite-text">${prerequisite}</div></div>`) +
        `
              <div class='card-desc'><div class='card-desc-scroller'>${card.description}</div></div>
              <div class='card-bottom-left-corner'></div>
              <div class='card-bottom-right-corner'></div>
              ` +
        (card.holder
          ? "<div class='resource-holder farmer-holder resource-holder-update " +
            (card.actionCard ? 'actionCard' : '') +
            `'>${subHolders}</div>`
          : '') +
        `
            </div>
            <div class="player-card-zoom">
              <svg><use href="#zoom-svg" /></svg>
            </div>
          </div>
          ` +
        (card.field ? "<div class='player-card-field-cell'></div>" : '') +
        (!tooltip && card.animalHolder
          ? "<div class='resource-holder resource-holder-update animal-holder' data-n='0'></div>"
          : '') +
        `
        </div>`
      );
    },

    /**
     * Format card cost
     */
    formatCardCost(card) {
      let formatArray = (arr) =>
        Object.keys(arr)
          .map((res) => '<div>' + this.formatStringMeeples(arr[res] + '<' + res.toUpperCase() + '>') + '</div>')
          .join('');

      return (
        (card.fee != null ? formatArray(card.fee) + '<div class="card-cost-fee-separator">+</div>' : '') +
        card.costs.map((cost) => formatArray(cost)).join('<div class="card-cost-separator"></div>')
      );
    },

    /**
     * Prompt current player to pick a card
     */
    promptCard(types, cards, callback) {
      if (this.isFastMode()) return;

      // Majors
      if (types.includes('major')) {
        dojo.query('#majors-container .player-card').addClass('unselectable');
        this.addPrimaryActionButton('btnOpenMajorModal', _('Show major improvements'), () => this.openMajorsModal());

        if (types.length == 1) {
          // If only major are prompted, auto open modal
          this.openMajorsModal();
        }
      }

      // Hand
      if (types.includes('minor') || types.includes('occupation')) {
        dojo.query('#hand-container .player-card').addClass('unselectable');
        if (this.prefs[HAND_CARDS].value == 0) {
          this.addPrimaryActionButton('btnOpenHandModal', _('Show hand cards'), () => this.openHandModal());

          if (types.length == 1) {
            // If only one type is prompted, auto open modal
            this.openHandModal();
          }
        }
      }

      // Add event listener
      cards.forEach((cardId) => this.onClick(cardId, () => callback(cardId)));
    },

    computeSlidingAnimationFrom(card, newContainer) {
      let from = 'hand-button';
      if (!$(card.id)) {
        this.addCard(card, newContainer);
        from = 'overall_player_board_' + card.pId;
      } else {
        dojo.place(card.id, newContainer);
        if (card.type == 'major') {
          from = this._majorsDialog.isDisplayed() ? card.id + '_holder' : 'majors-button';
        } else {
          from = this._handDialog.isDisplayed() || this.prefs[HAND_CARDS].value != 0 ? 'hand-container' : 'hand-button';
        }
      }

      this.updatePlayerBoardDimensions();
      return from;
    },

    /**
     * Notification when someone bought a card
     */
    notif_buyCard(n) {
      debug('Notif: buying a card', n);
      let card = n.args.card;

      dojo.query('.cards-wrapper .player-card.phantom').removeClass('phantom');

      let duration = 700;
      let waitingTime = 80000 / this._cardAnimationSpeed;

      // Create the card if needed, and compute initial location of sliding event
      let exists = $(card.id);
      let from = this.computeSlidingAnimationFrom(card, 'cards-wrapper-' + card.pId);

      // Zoom on it, then zoom off
      if (this.isFastMode()) {
        this.notifqueue.setSynchronousDuration(0);
      } else {
        this.zoomOnCard(card.id, { from, duration })
          .then(() => this.wait(waitingTime))
          .then(() => this.zoomOffCard({ duration }))
          .then(() => this.notifqueue.setSynchronousDuration(10));
      }

      // If the card was already existing, make sure to add event listener for zooming
      if (exists) {
        dojo.addClass(card.id, 'mini');
        $(card.id)
          .querySelector('.player-card-zoom')
          .addEventListener('click', () => {
            this.zoomOnCard(card.id);
          });
      }

      // Close major modal if open
      if (this._majorsDialog.isDisplayed()) {
        this._majorsDialog.hide();
      }
      // Close hand modal if open
      if (this._handDialog.isDisplayed()) {
        this._handDialog.hide();
      }

      return null;
    },

    /**
     * Notification when someone bought a card and give it to next player
     */
    notif_buyAndPassCard(n) {
      debug('Notif: buying and passing a card', n);
      let card = n.args.card;
      let receiving = this.player_id == n.args.player_id2;

      let duration = 700;
      let waitingTime = 80000 / this._cardAnimationSpeed;

      // Create the card if needed, and compute initial location of sliding event
      let from = this.computeSlidingAnimationFrom(card, receiving ? 'hand-button' : 'reserve-' + n.args.player_id2);
      if (this.isFastMode()) {
        if (receiving) {
          dojo.place(card.id, 'hand-container');
        } else {
          dojo.destroy(card.id);
        }
        this.notifqueue.setSynchronousDuration(0);
        return;
      }

      // Zoom on it, then zoom off
      this.zoomOnCard(card.id, { from, duration })
        .then(() => this.wait(waitingTime))
        .then(() => this.zoomOffCard({ duration }))
        .then(() => {
          if (receiving) {
            dojo.place(card.id, 'hand-container');
          } else {
            dojo.destroy(card.id);
          }
          this.notifqueue.setSynchronousDuration(10);
        });

      // Close hand modal if open
      if (this._handDialog.isDisplayed()) {
        this._handDialog.hide();
      }
    },

    notif_buyAndDestroyCard(n) {
      debug('Notif: buying and destroying a card', n);
      let card = n.args.card;

      let duration = 700;
      let waitingTime = 80000 / this._cardAnimationSpeed;

      // Create the card if needed, and compute initial location of sliding event
      let from = this.computeSlidingAnimationFrom(card, 'reserve-' + n.args.player_id);
      // Zoom on it, then zoom off
      this.zoomOnCard(card.id, { from, duration })
        .then(() => this.wait(waitingTime))
        .then(() => this.zoomOffCard({ duration }))
        .then(() => {
          dojo.destroy(card.id);
          this.notifqueue.setSynchronousDuration(10);
        });

      // Close hand modal if open
      if (this._handDialog.isDisplayed()) {
        this._handDialog.hide();
      }
    },

    /**
     * Notification when someone return a card for a payment
     */
    notif_payWithCard(n) {
      debug('Notif: return a card', n);
      let card = n.args.card;
      if (this.isFastMode()) {
        dojo.place(card.id, card.id + '_holder');
        dojo.removeClass(card.id, 'mini');
        return;
      }

      dojo.style(card.id, 'transform', 'scale(0.6)');
      this.slide(card.id, 'majors-button').then(() => {
        dojo.place(card.id, card.id + '_holder');
        dojo.removeClass(card.id, 'mini');
      });
      dojo.removeClass(card.id, 'mini');
      dojo.style(card.id, 'transform', 'scale(1)');
    },

    /**
     * Zoom on a card
     */
    zoomOnCard(cardId, config = {}) {
      this._zoomedCard = cardId;
      let originalCard = $(cardId);
      let animatedCard = dojo.clone(originalCard);
      let scale =
        (parseFloat(this.getScale(originalCard.querySelector('.player-card-resizable'))) * 100) /
        parseInt(this._cardScale);

      dojo.addClass(originalCard, 'phantom'); // Make the original card invisible
      dojo.attr(animatedCard, 'id', cardId + '_animated'); // Add a prefix to avoid duplicate id
      dojo.style(animatedCard, 'transform', `scale(${scale})`);
      dojo.empty('card-overlay');
      dojo.place(animatedCard, 'card-overlay');

      // Start animation
      config.from = config.from || originalCard;
      let anim = this.slide(animatedCard, 'card-overlay', config);
      dojo.addClass('card-overlay', 'active');
      dojo.style(animatedCard, 'transform', `scale(${100 / parseInt(this._cardScale)})`);
      dojo.removeClass(animatedCard, 'mini');
      return anim;
    },

    /**
     * Zoom off a card
     */
    zoomOffCard(config = {}) {
      if (this._zoomedCard == null) return;

      let cardId = this._zoomedCard;
      let originalCard = $(cardId);
      let animatedCard = $(cardId + '_animated');
      let scale =
        (parseFloat(this.getScale(originalCard.querySelector('.player-card-resizable'))) * 100) /
        parseInt(this._cardScale);

      config.destroy = true;
      let anim = this.slide(animatedCard, cardId, config).then(() => dojo.removeClass(originalCard, 'phantom'));
      dojo.removeClass('card-overlay', 'active');
      dojo.style(animatedCard, 'transform', `scale(${scale})`);
      dojo.addClass(animatedCard, 'mini');
      this._zoomedCard = null;
      return anim;
    },

    /********************
     ******* DRAFT *******
     *********************/
    onEnteringStateDraftPlayers(args) {
      if (this.isSpectator) return;

      // Toggle draft
      this._isDraft = true;
      this.updateHandContainer();

      // Add cards and listeners
      dojo.query('#draft-container .player-card').forEach(dojo.destroy);
      let cards = args._private ? args._private : this.gamedatas.draft;
      cards.forEach((card) => {
        let cardId = card.id;
        if (!$(cardId)) {
          this.addCard(card);
          this.slideFromLeft(cardId);
        }

        if (!this.isReadOnly()) {
          this.onClick(cardId, () => this.onClickCardDraft(cardId));
        }
      });

      // Update action button
      this._draftType = args.type;
      this.updateDraftStatus();
    },

    getDraftSelection() {
      return {
        occupation: dojo.query('.player-card.occupation.pending').length,
        minor: dojo.query('.player-card.minor.pending').length,
      };
    },

    onClickCardDraft(cardId) {
      if (dojo.hasClass(cardId, 'unselectable')) {
        return;
      }

      if (dojo.hasClass(cardId, 'pending')) {
        this.takeAction('actDraftRemove', { cardId }, false);
      } else {
        this.takeAction('actDraftAdd', { cardId });
      }
    },

    notif_addCardToDraftSelection(n) {
      debug('Notif: add card to draft selection', n);
      let cardId = n.args.cardId;
      dojo.addClass(cardId, 'pending');
      dojo.attr(cardId, 'data-state', n.args.pos);
      this.updateDraftStatus();

      // Compute position using state data-attr
      let cards = [...$('hand-container').querySelectorAll('.player-card')];
      let brother = cards.reduce(
        (carry, card) => carry || (card.getAttribute('data-state') > n.args.pos ? card : carry),
        null,
      );
      // Slide it
      this.slide(cardId, 'hand-container', {
        phantom: true,
        beforeBrother: brother,
      });
      if (!this.isFastMode()) {
        sortable('#hand-container');
      }
    },
    notif_removeCardFromDraftSelection(n) {
      debug('Notif: remove card to draft selection', n);
      let cardId = n.args.cardId;
      dojo.removeClass(cardId, 'pending');
      this.updateDraftStatus();
      this.slide(cardId, 'draft-container', { phantom: true });
    },

    updateDraftStatus() {
      let selection = this.getDraftSelection();
      let allSet = true;

      // Check minors
      if (selection.minor == this._draftType.minor) {
        dojo.query('#draft-container .player-card.minor:not(.pending)').addClass('unselectable');
      } else {
        dojo.query('#draft-container .player-card.minor:not(.pending)').removeClass('unselectable');
        allSet = false;
      }

      // Check occupations
      if (selection.occupation == this._draftType.occupation) {
        dojo.query('#draft-container .player-card.occupation:not(.pending)').addClass('unselectable');
      } else {
        dojo.query('#draft-container .player-card.occupation:not(.pending)').removeClass('unselectable');
        allSet = false;
      }

      dojo.destroy('btnConfirmDraft');
      if (allSet && this.isCurrentPlayerActive()) {
        this.addPrimaryActionButton('btnConfirmDraft', _('Confirm selection'), () =>
          this.takeAction('actDraftConfirm'),
        );
      }
    },

    onUpdateActivityDraftPlayers(args, stats) {
      if (this._draftType != null) this.updateDraftStatus();
    },

    notif_confirmDraftSelection(n) {
      debug('Notif: confirming draft selection', n);
      dojo.removeClass(n.args.card.id, 'pending');
    },
    notif_clearDraftPools(n) {
      debug('Notif: clearing draft pools', n);
      dojo.query('#draft-container .player-card').forEach((oCard) => this.slideToRight(oCard));
    },

    notif_draftIsOver(n) {
      debug('Notif: draft is over');

      // Toogle off draft
      dojo.query('#draft-container .player-card').forEach(dojo.destroy);
      this._isDraft = false;
      this.updateHandContainer();
    },

    slideFromLeft(elem) {
      if (this.isFastMode()) return;
      elem = typeof elem == 'string' ? $(elem) : elem;
      let x = elem.offsetWidth + elem.offsetLeft + 30;
      dojo.addClass(elem, 'notransition');
      dojo.style(elem, 'opacity', '0');
      dojo.style(elem, 'left', -x + 'px');
      elem.offsetHeight;
      dojo.removeClass(elem, 'notransition');

      dojo.style(elem, 'opacity', '1');
      dojo.style(elem, 'left', '0px');
    },

    slideToRight(elem) {
      if (this.isFastMode()) return;
      elem = typeof elem == 'string' ? $(elem) : elem;
      let stack = elem.parentNode;
      let x = stack.offsetWidth - elem.offsetLeft + 100;
      dojo.style(elem, 'left', x + 'px');
    },
  });
});
