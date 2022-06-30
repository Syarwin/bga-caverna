<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * caverna implementation : © Timothée Pecatte <tim.pecatte@gmail.com>, Vincent Toper <vincent.toper@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
 * See http://en.boardgamearena.com/#!doc/Studio for more information.
 * -----
 *
 * gameoptions.inc.php
 *
 * caverna game options description
 *
 */

namespace AGR;

require_once 'modules/php/constants.inc.php';

$startAction = totranslate('Can only be used in a multiplayer game');
$begAction = totranslate('Draft cannot be enabled in beginner mode');

$game_options = [
  OPTION_COMPETITIVE_LEVEL => [
    'name' => totranslate('Competitive level'),
    'values' => [
      OPTION_COMPETITIVE_BEGINNER => [
        'name' => totranslate('Beginner'),
        'tmdisplay' => totranslate('Beginner'),
        'description' => totranslate('No hand cards'),
      ],
      OPTION_COMPETITIVE_NORMAL => [
        'name' => totranslate('Normal'),
        'nobeginner' => true,
      ],
      OPTION_COMPETITIVE_BANLIST => [
        'name' => totranslate('Competitive'),
        'tmdisplay' => totranslate('Banlist'),
        'description' => totranslate(
          'Banlist mode removing overpowered cards: B10_Caravan, A133_Braggart, C3_CarriageTrip, D4_CrossCutWood, D97_BeggingStudent, A33_BigCountry'
        ),
        'nobeginner' => true,
      ],
    ],
    'default' => OPTION_COMPETITIVE_BEGINNER,
  ],

  OPTION_ADDITIONAL_SPACES => [
    'name' => totranslate('Additional Action Spaces'),
    'values' => [
      OPTION_ADDITIONAL_SPACES_ENABLED => [
        'name' => totranslate('Enabled (variant)'),
        'tmdisplay' => totranslate('Additional Spaces'),
        'description' => totranslate('Additional action space tile'),
      ],
      OPTION_ADDITIONAL_SPACES_DISABLED => [
        'name' => totranslate('Disabled (standard)'),
        'nobeginner' => true,
      ],
    ],
    'default' => OPTION_ADDITIONAL_SPACES_DISABLED,
    'startcondition' => [
      OPTION_ADDITIONAL_SPACES_ENABLED => [
        [
          'type' => 'minplayers',
          'value' => 2,
          'message' => totranslate('Additional action spaces needs at least 2 players'),
        ],
      ],
    ],
  ],

  OPTION_DECK_CD => [
    'name' => totranslate('C&D cards'),
    'values' => [
      OPTION_DECK_DISABLED => [
        'name' => totranslate('Excluded'),
      ],
      OPTION_DECK_ENABLED => [
        'name' => totranslate('Included'),
        'tmdisplay' => totranslate('[C&D]'),
        'beta' => true,
      ],
    ],
    'displaycondition' => [
      [
        'type' => 'otheroptionisnot',
        'id' => OPTION_COMPETITIVE_LEVEL,
        'value' => OPTION_COMPETITIVE_BEGINNER,
      ],
    ],
  ],

  OPTION_NEW_SET => [
    'name' => totranslate('New set of cards'),
    'values' => [
      OPTION_DECK_DISABLED => [
        'name' => totranslate('Excluded'),
      ],
      OPTION_DECK_ENABLED => [
        'name' => totranslate('Included'),
        'tmdisplay' => totranslate('[New]'),
        'alpha' => true,
      ],
    ],
    'displaycondition' => [
      [
        'type' => 'otheroptionisnot',
        'id' => OPTION_COMPETITIVE_LEVEL,
        'value' => OPTION_COMPETITIVE_BEGINNER,
      ],
    ],
  ],


  /*
  OPTION_DECK_A => [
    'name' => totranslate('Deck A cards'),
    'values' => [
      OPTION_DECK_ENABLED => [
        'name' => totranslate('Included'),
        'tmdisplay' => totranslate('[A]'),
      ],
      OPTION_DECK_DISABLED => [
        'name' => totranslate('Excluded'),
      ],
    ],
    'default' => OPTION_DECK_ENABLED,
    'displaycondition' => [
      [
        'type' => 'otheroptionisnot',
        'id' => OPTION_BEGINNER,
        'value' => OPTION_BEGINNER_ENABLED,
      ],
    ],
  ],

  OPTION_DECK_B => [
    'name' => totranslate('Deck B cards'),
    'values' => [
      OPTION_DECK_ENABLED => [
        'name' => totranslate('Included'),
        'tmdisplay' => totranslate('[B]'),
      ],
      OPTION_DECK_DISABLED => [
        'name' => totranslate('Excluded'),
      ],
    ],
    'default' => OPTION_DECK_ENABLED,
    'displaycondition' => [
      [
        'type' => 'otheroptionisnot',
        'id' => OPTION_BEGINNER,
        'value' => OPTION_BEGINNER_ENABLED,
      ],
    ],
  ],
*/

  OPTION_DRAFT => [
    'name' => totranslate('Draft mode'),
    'values' => [
      OPTION_DRAFT_DISABLED => [
        'name' => totranslate('Disabled'),
      ],

      OPTION_SEED_MODE => [
        'name' => totranslate('Seed mode'),
        'tmdisplay' => totranslate('[Custom seed]'),
        'description' => totranslate(
          'Allow to replay a previous game with same cards and same order for action cards. Do NOT select this if you don\'t have a seed from a previous game'
        ),
      ],

      OPTION_PICK_7_OUT_OF_10 => [
        'name' => totranslate('Keep 7 cards out of 10 dealt'),
        'tmdisplay' => totranslate('[Keep 7 out of 10]'),
      ],

      OPTION_DRAFT_7_SIMULTANEOUS => [
        'name' => totranslate('Draft 7 cards, Occupations with Minors'),
        'tmdisplay' => totranslate('[Draft 7, Occ + MI]'),
      ],
      OPTION_DRAFT_8_SIMULTANEOUS => [
        'name' => totranslate('Draft 7 out of 8 cards, Occupations with Minors'),
        'tmdisplay' => totranslate('[Draft 8 -> 7, Occ + MI]'),
      ],
      OPTION_DRAFT_9_SIMULTANEOUS => [
        'name' => totranslate('Draft 7 out of 9 cards, Occupations with Minors'),
        'tmdisplay' => totranslate('[Draft 9 -> 7, Occ + MI]'),
      ],
      OPTION_DRAFT_10_SIMULTANEOUS => [
        'name' => totranslate('Draft 7 out of 10 cards, Occupations with Minors'),
        'tmdisplay' => totranslate('[Draft 10 -> 7, Occ + MI]'),
      ],

      OPTION_DRAFT_7_OCCUPATIONS => [
        'name' => totranslate('Draft 7 cards, Occupations then Minors'),
        'tmdisplay' => totranslate('[Draft 7, Occ first]'),
      ],
      OPTION_DRAFT_8_OCCUPATIONS => [
        'name' => totranslate('Draft 7 out of 8 cards, Occupations then Minors'),
        'tmdisplay' => totranslate('[Draft 8 -> 7, Occ first]'),
      ],
      OPTION_DRAFT_9_OCCUPATIONS => [
        'name' => totranslate('Draft 7 out of 9 cards, Occupations then Minors'),
        'tmdisplay' => totranslate('[Draft 9 -> 7, Occ first]'),
      ],
      OPTION_DRAFT_10_OCCUPATIONS => [
        'name' => totranslate('Draft 7 out of 10 cards, Occupations then Minors'),
        'tmdisplay' => totranslate('[Draft 10 -> 7, Occ first]'),
      ],

      OPTION_DRAFT_7_MINORS => [
        'name' => totranslate('Draft 7 cards, Minors then Occupations'),
        'tmdisplay' => totranslate('[Draft 7, MI first]'),
      ],
      OPTION_DRAFT_8_MINORS => [
        'name' => totranslate('Draft 7 out of 8 cards, Minors then Occupations'),
        'tmdisplay' => totranslate('[Draft 8 -> 7, MI first]'),
      ],
      OPTION_DRAFT_9_MINORS => [
        'name' => totranslate('Draft 7 out of 9 cards, Minors then Occupations'),
        'tmdisplay' => totranslate('[Draft 9 -> 7, MI first]'),
      ],
      OPTION_DRAFT_10_MINORS => [
        'name' => totranslate('Draft 7 out of 10 cards, Minors then Occupations'),
        'tmdisplay' => totranslate('[Draft 10 -> 7, MI first]'),
      ],
      OPTION_DRAFT_FREE => [
        'name' => totranslate('Draft 7 out of all the cards (solo mode)'),
      ],
    ],
    'default' => OPTION_DRAFT_DISABLED,
    'startcondition' => [
      OPTION_DRAFT_FREE => [
        ['type' => 'maxplayers', 'value' => 1, 'message' => totranslate('Can only be used with one player')],
      ],
      OPTION_DRAFT_7_SIMULTANEOUS => [['type' => 'minplayers', 'value' => 2, 'message' => $startAction]],
      OPTION_DRAFT_8_SIMULTANEOUS => [['type' => 'minplayers', 'value' => 2, 'message' => $startAction]],
      OPTION_DRAFT_9_SIMULTANEOUS => [['type' => 'minplayers', 'value' => 2, 'message' => $startAction]],
      OPTION_DRAFT_10_SIMULTANEOUS => [['type' => 'minplayers', 'value' => 2, 'message' => $startAction]],
      OPTION_DRAFT_7_OCCUPATIONS => [['type' => 'minplayers', 'value' => 2, 'message' => $startAction]],
      OPTION_DRAFT_8_OCCUPATIONS => [['type' => 'minplayers', 'value' => 2, 'message' => $startAction]],
      OPTION_DRAFT_9_OCCUPATIONS => [['type' => 'minplayers', 'value' => 2, 'message' => $startAction]],
      OPTION_DRAFT_10_OCCUPATIONS => [['type' => 'minplayers', 'value' => 2, 'message' => $startAction]],
      OPTION_DRAFT_7_MINORS => [['type' => 'minplayers', 'value' => 2, 'message' => $startAction]],
      OPTION_DRAFT_8_MINORS => [['type' => 'minplayers', 'value' => 2, 'message' => $startAction]],
      OPTION_DRAFT_9_MINORS => [['type' => 'minplayers', 'value' => 2, 'message' => $startAction]],
      OPTION_DRAFT_10_MINORS => [['type' => 'minplayers', 'value' => 2, 'message' => $startAction]],
    ],
    'displaycondition' => [
      [
        'type' => 'otheroptionisnot',
        'id' => OPTION_COMPETITIVE_LEVEL,
        'value' => OPTION_COMPETITIVE_BEGINNER,
      ],
    ],
  ],

  OPTION_SCORING => [
    'name' => totranslate('Live scoring'),
    'values' => [
      OPTION_SCORING_ENABLED => [
        'name' => totranslate('Enabled'),
      ],
      OPTION_SCORING_DISABLED => [
        'name' => totranslate('Disabled'),
        'tmdisplay' => totranslate('No live scoring'),
      ],
    ],
    'default' => OPTION_SCORING_ENABLED,
  ],
];

$game_preferences = [
  OPTION_AUTOPAY_HARVEST => [
    'name' => totranslate('Automatically pay harvest if enough food (and no special exchanges)'),
    'needReload' => false,
    'values' => [
      OPTION_AUTOPAY_HARVEST_ENABLED => ['name' => totranslate('Pay automatic')],
      OPTION_AUTOPAY_HARVEST_DISABLED => ['name' => totranslate('Always enter "Exchange" state')],
    ],
  ],

  OPTION_CONFIRM => [
    'name' => totranslate('Turn confirmation'),
    'needReload' => false,
    'values' => [
      OPTION_CONFIRM_TIMER => ['name' => totranslate('Enabled with timer')],
      OPTION_CONFIRM_ENABLED => ['name' => totranslate('Enabled')],
      OPTION_CONFIRM_DISABLED => ['name' => totranslate('Disabled')],
    ],
  ],

  OPTION_COLORBLIND => [
    'name' => totranslate('Colorblind-friendly farmers'),
    'needReload' => false,
    'values' => [
      OPTION_COLORBLIND_OFF => ['name' => totranslate('Disabled')],
      OPTION_COLORBLIND_ON => ['name' => totranslate('Enabled')],
    ],
  ],

  OPTION_FONT_DOMINICAN => [
    'name' => totranslate('Text font'),
    'needReload' => false,
    'values' => [
      OPTION_FONT_DOMINICAN_ON => ['name' => totranslate('Dominican (original)')],
      OPTION_FONT_DOMINICAN_OFF => ['name' => totranslate('Roboto (BGA default)')],
    ],
  ],

  OPTION_SMART_REORGANIZE => [
    'name' => totranslate('Auto reorganize'),
    'needReload' => false,
    'values' => [
      OPTION_SMART_REORGANIZE_ON => ['name' => totranslate('Enabled without confirm')],
      OPTION_SMART_REORGANIZE_OFF => ['name' => totranslate('Always manual')],
      OPTION_SMART_REORGANIZE_CONFIRM => ['name' => totranslate('Enabled with confirm')],
    ],
  ],

  OPTION_PLAYER_RESOURCES => [
    'name' => totranslate('Resources bar location'),
    'needReload' => false,
    'values' => [
      OPTION_PLAYER_RESOURCES_PANNEL => ['name' => totranslate('In the top right pannels')],
      OPTION_PLAYER_RESOURCES_BOARD => ['name' => totranslate('Next to the player boards')],
    ],
  ],

  OPTION_PLAYER_BOARDS => [
    'name' => totranslate('Other player boards'),
    'needReload' => false,
    'values' => [
      OPTION_PLAYER_BOARDS_BOTTOM => ['name' => totranslate('Under the central board')],
      OPTION_PLAYER_BOARDS_RIGHT => ['name' => totranslate('Next to the central board')],
    ],
  ],

  OPTION_DISPLAY_CARDS => [
    'name' => totranslate('Display hand cards'),
    'needReload' => false,
    'values' => [
      OPTION_DISPLAY_CARDS_MODAL => ['name' => totranslate('In a modal window')],
      OPTION_DISPLAY_CARDS_BOARD => ['name' => totranslate('Under the board')],
      OPTION_DISPLAY_CARDS_BOTTOM => ['name' => totranslate('Bottom of the screen')],
    ],
  ],
];
