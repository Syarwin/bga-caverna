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
 * stats.inc.php
 *
 * caverna game statistics description
 *
 */

require_once 'modules/php/constants.inc.php';

$stats_type = [
  'table' => [],

  'value_labels' => [
    STAT_POSITION => [
      1 => totranslate('First player'),
      2 => totranslate('Second player'),
      3 => totranslate('Third player'),
      4 => totranslate('Fourth player'),
    ],
  ],

  'player' => [
    'position' => [
      'id' => STAT_POSITION,
      'name' => totranslate('Starting position in first round'),
      'type' => 'int',
    ],

    'scoreFields' => [
      'id' => STAT_SCORE_FIELDS,
      'name' => totranslate('Number of points earned by fields'),
      'type' => 'int',
    ],
    'scorePastures' => [
      'id' => STAT_SCORE_PASTURES,
      'name' => totranslate('Number of points earned by pastures'),
      'type' => 'int',
    ],
    'scoreGrains' => [
      'id' => STAT_SCORE_GRAINS,
      'name' => totranslate('Number of points earned by grain'),
      'type' => 'int',
    ],
    'scoreVegetables' => [
      'id' => STAT_SCORE_VEGETABLES,
      'name' => totranslate('Number of points earned by vegetables'),
      'type' => 'int',
    ],
    'scoreSheeps' => [
      'id' => STAT_SCORE_SHEEPS,
      'name' => totranslate('Number of points earned by sheep'),
      'type' => 'int',
    ],
    'scorePigs' => [
      'id' => STAT_SCORE_PIGS,
      'name' => totranslate('Number of points earned by wild boar'),
      'type' => 'int',
    ],
    'scoreCattles' => [
      'id' => STAT_SCORE_CATTLES,
      'name' => totranslate('Number of points earned by cattle'),
      'type' => 'int',
    ],
    'scoreUnused' => [
      'id' => STAT_SCORE_UNUSED,
      'name' => totranslate('Number of points lost by unused space'),
      'type' => 'int',
    ],
    'scoreStables' => [
      'id' => STAT_SCORE_STABLES,
      'name' => totranslate('Number of points earned by fenced stables'),
      'type' => 'int',
    ],
    'scoreClayRooms' => [
      'id' => STAT_SCORE_CLAY_ROOMS,
      'name' => totranslate('Number of points earned by clay hut rooms'),
      'type' => 'int',
    ],
    'scoreStoneRooms' => [
      'id' => STAT_SCORE_STONE_ROOMS,
      'name' => totranslate('Number of points earned by stone house rooms'),
      'type' => 'int',
    ],
    'scoreDwarves' => [
      'id' => STAT_SCORE_FARMERS,
      'name' => totranslate('Number of points earned by family size'),
      'type' => 'int',
    ],
    'scoreBeggings' => [
      'id' => STAT_SCORE_BEGGINGS,
      'name' => totranslate('Number of points lost by begging'),
      'type' => 'int',
    ],
    'scoreCards' => [
      'id' => STAT_SCORE_CARDS,
      'name' => totranslate('Number of points earned by cards'),
      'type' => 'int',
    ],
    'scoreCardsBonus' => [
      'id' => STAT_SCORE_CARDS_BONUS,
      'name' => totranslate('Number of bonus points earned by cards'),
      'type' => 'int',
    ],

    'firstPlayer' => [
      'id' => STAT_FIRST_PLAYER,
      'name' => totranslate('Number of times being first player of a round'),
      'type' => 'int',
    ],
    'placedDwarves' => [
      'id' => STAT_PLACED_FARMER,
      'name' => totranslate('Number of people placed on an action during the game'),
      'type' => 'int',
    ],

    'boardWood' => [
      'id' => STAT_WOOD_FROM_BOARD,
      'name' => totranslate('Amount of wood earned from the board'),
      'type' => 'int',
    ],
    'boardStone' => [
      'id' => STAT_STONE_FROM_BOARD,
      'name' => totranslate('Amount of stone earned from the board'),
      'type' => 'int',
    ],
    'boardOre' => [
      'id' => STAT_ORE_FROM_BOARD,
      'name' => totranslate('Amount of ore earned from the board'),
      'type' => 'int',
    ],
    'boardRuby' => [
      'id' => STAT_RUBY_FROM_BOARD,
      'name' => totranslate('Amount of ruby earned from the board'),
      'type' => 'int',
    ],
    'boardGrain' => [
      'id' => STAT_GRAIN_FROM_BOARD,
      'name' => totranslate('Amount of grains earned from the board'),
      'type' => 'int',
    ],
    'boardVegetable' => [
      'id' => STAT_VEGETABLE_FROM_BOARD,
      'name' => totranslate('Amount of vegetables earned from the board'),
      'type' => 'int',
    ],
    'boardFood' => [
      'id' => STAT_FOOD_FROM_BOARD,
      'name' => totranslate('Amount of food earned from the board'),
      'type' => 'int',
    ],
    'boardSheep' => [
      'id' => STAT_SHEEP_FROM_BOARD,
      'name' => totranslate('Amount of sheeps earned from the board'),
      'type' => 'int',
    ],
    'boardPig' => [
      'id' => STAT_PIG_FROM_BOARD,
      'name' => totranslate('Amount of wild boar earned from the board'),
      'type' => 'int',
    ],
    'boardCattle' => [
      'id' => STAT_CATTLE_FROM_BOARD,
      'name' => totranslate('Amount of cattles earned from the board'),
      'type' => 'int',
    ],

    'cardsWood' => [
      'id' => STAT_WOOD_FROM_CARDS,
      'name' => totranslate('Amount of wood earned from cards'),
      'type' => 'int',
    ],
    'cardsStone' => [
      'id' => STAT_STONE_FROM_CARDS,
      'name' => totranslate('Amount of stone earned from cards'),
      'type' => 'int',
    ],
    'cardsOre' => [
      'id' => STAT_ORE_FROM_CARDS,
      'name' => totranslate('Amount of ore earned from cards'),
      'type' => 'int',
    ],
    'cardsRuby' => [
      'id' => STAT_RUBY_FROM_CARDS,
      'name' => totranslate('Amount of ruby earned from cards'),
      'type' => 'int',
    ],
    'cardsGrain' => [
      'id' => STAT_GRAIN_FROM_CARDS,
      'name' => totranslate('Amount of grains earned from cards'),
      'type' => 'int',
    ],
    'cardsVegetable' => [
      'id' => STAT_VEGETABLE_FROM_CARDS,
      'name' => totranslate('Amount of vegetables earned from cards'),
      'type' => 'int',
    ],
    'cardsFood' => [
      'id' => STAT_FOOD_FROM_CARDS,
      'name' => totranslate('Amount of food earned from cards'),
      'type' => 'int',
    ],
    'cardsSheep' => [
      'id' => STAT_SHEEP_FROM_CARDS,
      'name' => totranslate('Amount of sheeps earned from cards'),
      'type' => 'int',
    ],
    'cardsPig' => [
      'id' => STAT_PIG_FROM_CARDS,
      'name' => totranslate('Amount of wild card earned from cards'),
      'type' => 'int',
    ],
    'cardsCattle' => [
      'id' => STAT_CATTLE_FROM_CARDS,
      'name' => totranslate('Amount of cattles earned from cards'),
      'type' => 'int',
    ],
    'cardsBegging' => [
      'id' => STAT_BEGGING_FROM_CARDS,
      'name' => totranslate('Amount of beggings earned from cards'),
      'type' => 'int',
    ],
    'totalMajorBuilt' => [
      'id' => STAT_MAJOR_BUILT,
      'name' => totranslate('Number of Major improvement built'),
      'type' => 'int', // done
    ],
    'totalMinorBuilt' => [
      'id' => STAT_MINOR_BUILT,
      'name' => totranslate('Number of Minor improvement built'),
      'type' => 'int', // done
    ],
    'totalOccupationBuilt' => [
      'id' => STAT_OCCUPATION_BUILT,
      'name' => totranslate('Number of Occupation built'),
      'type' => 'int', // done
    ],
    'totalRoomsBuilt' => [
      'id' => STAT_ROOMS_BUILT,
      'name' => totranslate('Number of rooms built'),
      'type' => 'int', // done
    ],
    'convertedGrain' => [
      'id' => STAT_GRAIN_CONVERTED,
      'name' => totranslate('Number of grain converted in food'),
      'type' => 'int',
    ],
    'convertedVegetable' => [
      'id' => STAT_VEGETABLE_CONVERTED,
      'name' => totranslate('Number of vegetables converted in food'),
      'type' => 'int',
    ],
    'convertedSheep' => [
      'id' => STAT_SHEEP_CONVERTED,
      'name' => totranslate('Number of sheep converted in food'),
      'type' => 'int',
    ],
    'convertedPig' => [
      'id' => STAT_PIG_CONVERTED,
      'name' => totranslate('Number of pig converted in food'),
      'type' => 'int',
    ],
    'convertedCattle' => [
      'id' => STAT_CATTLE_CONVERTED,
      'name' => totranslate('Number of cattle converted in food'),
      'type' => 'int',
    ],
    'convertedStone' => [
      'id' => STAT_STONE_CONVERTED,
      'name' => totranslate('Number of stone converted in food'),
      'type' => 'int',
    ],
    'convertedWood' => [
      'id' => STAT_WOOD_CONVERTED,
      'name' => totranslate('Number of wood converted in food'),
      'type' => 'int',
    ],
    'convertedOre' => [
      'id' => STAT_ORE_CONVERTED,
      'name' => totranslate('Number of ore converted in food'),
      'type' => 'int',
    ],
    'convertedRuby' => [
      'id' => STAT_RUBY_CONVERTED,
      'name' => totranslate('Number of ruby converted in food'),
      'type' => 'int',
    ],
    //
    'grainToFood' => [
      'id' => STAT_FOOD_FROM_GRAIN,
      'name' => totranslate('Number of food from grain'),
      'type' => 'int',
    ],
    'vegetableToFood' => [
      'id' => STAT_FOOD_FROM_VEGETABLE,
      'name' => totranslate('Number of food from vegetable'),
      'type' => 'int',
    ],
    'sheepToFood' => [
      'id' => STAT_FOOD_FROM_SHEEP,
      'name' => totranslate('Number of food from sheep'),
      'type' => 'int',
    ],
    'pigToFood' => [
      'id' => STAT_FOOD_FROM_PIG,
      'name' => totranslate('Number of food from pig'),
      'type' => 'int',
    ],
    'cattleToFood' => [
      'id' => STAT_FOOD_FROM_CATTLE,
      'name' => totranslate('Number of food from cattle'),
      'type' => 'int',
    ],
    'stoneToFood' => [
      'id' => STAT_FOOD_FROM_STONE,
      'name' => totranslate('Number of food from stone'),
      'type' => 'int',
    ],
    'woodToFood' => [
      'id' => STAT_FOOD_FROM_WOOD,
      'name' => totranslate('Number of food from wood'),
      'type' => 'int',
    ],
    'oreToFood' => [
      'id' => STAT_FOOD_FROM_ORE,
      'name' => totranslate('Number of food from ore'),
      'type' => 'int',
    ],
    'rubyToFood' => [
      'id' => STAT_FOOD_FROM_RUBY,
      'name' => totranslate('Number of food from ruby'),
      'type' => 'int',
    ],

    'harvestedGrain' => [
      'id' => STAT_HARVESTED_GRAINS,
      'name' => totranslate('Number of harvested grains'),
      'type' => 'int',
    ],
    'harvestedVegetable' => [
      'id' => STAT_HARVESTED_VEGETABLES,
      'name' => totranslate('Number of harvested vegetables'),
      'type' => 'int',
    ],
  ],
];
