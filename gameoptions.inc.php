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

namespace CAV;

require_once 'modules/php/constants.inc.php';

$game_options = [
  OPTION_COMPETITIVE_LEVEL => [
    'name' => totranslate('Competitive level'),
    'values' => [
      OPTION_COMPETITIVE_BEGINNER => [
        'name' => totranslate('Beginner'),
        'tmdisplay' => totranslate('Beginner'),
        'description' => totranslate('Less buildings'),
      ],
      OPTION_COMPETITIVE_NORMAL => [
        'name' => totranslate('Normal'),
        'nobeginner' => true,
      ],
    ],
    'default' => OPTION_COMPETITIVE_BEGINNER,
  ],

  \OPTION_REVEAL_HARVEST => [
    'name' => totranslate('Reveal harvest'),
    'values' => [
      OPTION_REVEAL_START => [
        'name' => totranslate('Start of turn'),
      ],
      \OPTION_REVEAL_END => [
        'name' => totranslate('Beginning of harvest'),
        'nobeginner' => true,
        'tmdisplay' => totranslate('Harvest type revealed just before the harvest'),
      ],
    ],
    'default' => OPTION_REVEAL_START,
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
