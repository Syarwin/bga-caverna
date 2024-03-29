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

    'scoreDog' => [
      'id' => STAT_SCORE_DOGS,
      'name' => totranslate('Number of points earned by dogs'),
      'type' => 'int',
    ],
    'scoreSheep' => [
      'id' => STAT_SCORE_SHEEPS,
      'name' => totranslate('Number of points earned by sheep'),
      'type' => 'int',
    ],
    'scorePig' => [
      'id' => STAT_SCORE_PIGS,
      'name' => totranslate('Number of points earned by wild boar'),
      'type' => 'int',
    ],
    'scoreCattle' => [
      'id' => STAT_SCORE_CATTLES,
      'name' => totranslate('Number of points earned by cattle'),
      'type' => 'int',
    ],
    'scoreDonkey' => [
      'id' => STAT_SCORE_DONKEYS,
      'name' => totranslate('Number of points earned by donkeys'),
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

    'scoreRubies' => [
      'id' => STAT_SCORE_RUBIES,
      'name' => totranslate('Number of points earned by rubies'),
      'type' => 'int',
    ],
    'scoreDwarfs' => [
      'id' => STAT_SCORE_DWARFS,
      'name' => totranslate('Number of points earned by dwarfs'),
      'type' => 'int',
    ],

    'scoreUnused' => [
      'id' => STAT_SCORE_UNUSED,
      'name' => totranslate('Number of points lost by unused space'),
      'type' => 'int',
    ],
    'scorePastures' => [
      'id' => STAT_SCORE_PASTURES,
      'name' => totranslate('Number of points earned by pastures'),
      'type' => 'int',
    ],
    'scoreMines' => [
      'id' => STAT_SCORE_MINES,
      'name' => totranslate('Number of points earned by stone house rooms'),
      'type' => 'int',
    ],

    'scoreBuildings' => [
      'id' => STAT_SCORE_BUILDINGS,
      'name' => totranslate('Number of points earned by buildings'),
      'type' => 'int',
    ],
    'scoreBuildingsBonus' => [
      'id' => STAT_SCORE_BUILDINGS_BONUS,
      'name' => totranslate('Number of bonus points earned by buildings'),
      'type' => 'int',
    ],

    'scoreGold' => [
      'id' => STAT_SCORE_GOLD,
      'name' => totranslate('Number of points earn by gold coins'),
      'type' => 'int',
    ],

    'scoreBeggings' => [
      'id' => STAT_SCORE_BEGGINGS,
      'name' => totranslate('Number of points lost by begging'),
      'type' => 'int',
    ],

    'firstPlayer' => [
      'id' => STAT_FIRST_PLAYER,
      'name' => totranslate('Number of times being first player of a round'),
      'type' => 'int',
    ],
    'placedDwarfs' => [
      'id' => STAT_PLACED_DWARF,
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
    'boardDonkey' => [
      'id' => STAT_DONKEY_FROM_BOARD,
      'name' => totranslate('Amount of donkeys earned from the board'),
      'type' => 'int',
    ],
    'boardDog' => [
      'id' => STAT_DOG_FROM_BOARD,
      'name' => totranslate('Amount of dogs earned from the board'),
      'type' => 'int',
    ],
    'boardGold' => [
      'id' => STAT_GOLD_FROM_BOARD,
      'name' => totranslate('Amount of gold earned from the board'),
      'type' => 'int',
    ],

    'buildingsWood' => [
      'id' => STAT_WOOD_FROM_BUILDINGS,
      'name' => totranslate('Amount of wood earned from buildings'),
      'type' => 'int',
    ],
    'buildingsStone' => [
      'id' => STAT_STONE_FROM_BUILDINGS,
      'name' => totranslate('Amount of stone earned from buildings'),
      'type' => 'int',
    ],
    'buildingsOre' => [
      'id' => STAT_ORE_FROM_BUILDINGS,
      'name' => totranslate('Amount of ore earned from buildings'),
      'type' => 'int',
    ],
    'buildingsRuby' => [
      'id' => STAT_RUBY_FROM_BUILDINGS,
      'name' => totranslate('Amount of ruby earned from buildings'),
      'type' => 'int',
    ],
    'buildingsGrain' => [
      'id' => STAT_GRAIN_FROM_BUILDINGS,
      'name' => totranslate('Amount of grains earned from buildings'),
      'type' => 'int',
    ],
    'buildingsVegetable' => [
      'id' => STAT_VEGETABLE_FROM_BUILDINGS,
      'name' => totranslate('Amount of vegetables earned from buildings'),
      'type' => 'int',
    ],
    'buildingsFood' => [
      'id' => STAT_FOOD_FROM_BUILDINGS,
      'name' => totranslate('Amount of food earned from buildings'),
      'type' => 'int',
    ],
    'buildingsSheep' => [
      'id' => STAT_SHEEP_FROM_BUILDINGS,
      'name' => totranslate('Amount of sheeps earned from buildings'),
      'type' => 'int',
    ],
    'buildingsPig' => [
      'id' => STAT_PIG_FROM_BUILDINGS,
      'name' => totranslate('Amount of wild card earned from buildings'),
      'type' => 'int',
    ],
    'buildingsCattle' => [
      'id' => STAT_CATTLE_FROM_BUILDINGS,
      'name' => totranslate('Amount of cattles earned from buildings'),
      'type' => 'int',
    ],
    'buildingsDonkey' => [
      'id' => STAT_DONKEY_FROM_BUILDINGS,
      'name' => totranslate('Amount of donkeys earned from buildings'),
      'type' => 'int',
    ],
    'buildingsDog' => [
      'id' => STAT_DOG_FROM_BUILDINGS,
      'name' => totranslate('Amount of dogs earned from buildings'),
      'type' => 'int',
    ],
    'buildingsGold' => [
      'id' => STAT_GOLD_FROM_BUILDINGS,
      'name' => totranslate('Amount of gold earned from buildings'),
      'type' => 'int',
    ],
    'buildingsBegging' => [
      'id' => STAT_BEGGING_FROM_BUILDINGS,
      'name' => totranslate('Amount of beggings earned from buildings'),
      'type' => 'int',
    ],
    'totalBuildingsBuilt' => [
      'id' => STAT_BUILDINGS_BUILT,
      'name' => totranslate('Number of buildings built'),
      'type' => 'int',
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
    'convertedDonkey' => [
      'id' => STAT_DONKEY_CONVERTED,
      'name' => totranslate('Number of donkey converted in food'),
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
    'convertedGold' => [
      'id' => STAT_GOLD_CONVERTED,
      'name' => totranslate('Number of gold converted in food'),
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
    'donkeyToFood' => [
      'id' => STAT_FOOD_FROM_DONKEY,
      'name' => totranslate('Number of food from donkey'),
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
    'goldToFood' => [
      'id' => STAT_FOOD_FROM_GOLD,
      'name' => totranslate('Number of food from gold'),
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
