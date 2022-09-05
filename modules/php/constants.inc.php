<?php

/*
 * Game options
 */
const OPTION_COMPETITIVE_LEVEL = 102;
const OPTION_COMPETITIVE_BEGINNER = 0;
const OPTION_COMPETITIVE_NORMAL = 1;

const OPTION_SCORING = 107;
const OPTION_SCORING_ENABLED = 0;
const OPTION_SCORING_DISABLED = 1;

const OPTION_REVEAL_HARVEST = 110;
const OPTION_REVEAL_START = 0;
const OPTION_REVEAL_END = 1;

/*
 * User preferences
 */
const OPTION_AUTOPAY_HARVEST = 102;
const OPTION_AUTOPAY_HARVEST_ENABLED = 0;
const OPTION_AUTOPAY_HARVEST_DISABLED = 1;

const OPTION_CONFIRM = 103;
const OPTION_CONFIRM_DISABLED = 0;
const OPTION_CONFIRM_TIMER = 1;
const OPTION_CONFIRM_ENABLED = 2;

const OPTION_COLORBLIND = 104;
const OPTION_COLORBLIND_OFF = 0;
const OPTION_COLORBLIND_ON = 1;

const OPTION_FONT_DOMINICAN = 105;
const OPTION_FONT_DOMINICAN_ON = 0;
const OPTION_FONT_DOMINICAN_OFF = 1;

const OPTION_SMART_REORGANIZE = 106;
const OPTION_SMART_REORGANIZE_ON = 0;
const OPTION_SMART_REORGANIZE_OFF = 1;
const OPTION_SMART_REORGANIZE_CONFIRM = 2;

const OPTION_PLAYER_BOARDS = 107;
const OPTION_PLAYER_BOARDS_BOTTOM = 0;
const OPTION_PLAYER_BOARDS_RIGHT = 1;

const OPTION_DISPLAY_CARDS = 108;
const OPTION_DISPLAY_CARDS_MODAL = 0;
const OPTION_DISPLAY_CARDS_BOARD = 1;
const OPTION_DISPLAY_CARDS_BOTTOM = 2;

const OPTION_PLAYER_RESOURCES = 109;
const OPTION_PLAYER_RESOURCES_PANNEL = 0;
const OPTION_PLAYER_RESOURCES_BOARD = 1;

/*
 * State constants
 */
const ST_GAME_SETUP = 1;
const ST_GENERIC_NEXT_PLAYER = 97;

const ST_START_GAME = 2;
const ST_LOAD_SEED = 3;

const ST_BEFORE_START_OF_TURN = 4;
const ST_PREPARATION = 5;
const ST_NEXT_PLAYER_LABOR = 6;
const ST_LABOR = 7;
const ST_END_WORK_PHASE = 8;
const ST_RESOLVE_STACK = 10;
const ST_RESOLVE_CHOICE = 11;

const ST_PLACE_DWARF = 20;
const ST_GAIN = 21;
const ST_IMITATION = 22;
const ST_PAY = 23;
const ST_COLLECT = 24;
const ST_FIRSTPLAYER = 25;
const ST_PLACE_TILE = 28;
const ST_SOW = 29;
const ST_STABLE = 30;
const ST_WISHCHILDREN = 33;
const ST_EXCHANGE = 34;
const ST_ACTIVATE_BUILDING = 35;
const ST_SPECIAL_EFFECT = 36;
const ST_RECEIVE = 37;
const ST_REAP = 38;
const ST_PLACE_FUTURE_MEEPLES = 39;
const ST_BLACKSMITH = 41;
const ST_EXPEDITION = 42;
const ST_WEAPON_INCREASE = 43;
const ST_FURNISH = 44;
const ST_BREED = 45;

const ST_REORGANIZE = 80;

const ST_START_HARVEST = 70;
const ST_HARVEST_FIELD = 71;
const ST_HARVEST_FEED = 72;
const ST_HARVEST_BREED = 73;

const ST_PRE_END_OF_TURN = 40;
const ST_END_OF_TURN = 9;

const ST_IMPOSSIBLE_MANDATORY_ACTION = 92;

const ST_CONFIRM_TURN = 90;
const ST_CONFIRM_PARTIAL_TURN = 91;

const ST_PRE_END_OF_GAME = 98;
const ST_END_GAME = 99;

/*
 * ENGINE
 */
const NODE_SEQ = 'seq';
const NODE_OR = 'or';
const NODE_XOR = 'xor';
const NODE_PARALLEL = 'parallel';
const NODE_LEAF = 'leaf';
const NODE_THEN_OR = 'thenOr';

const ZOMBIE = 98;
const PASS = 99;
/*
 * Types of cards
 */
const ACTION = 0;
const MAJOR = 'major';
const MINOR = 'minor';
const OCCUPATION = 'occupation';

const VISIBLE = 1;
const HIDDEN = 0;

const INFTY = 9999;
/*
 * Card categories
 */
const ACTIONS_BOOSTER = 'BoosterCategory';
const LIVESTOCK_PROVIDER = 'LivestockCategory';
const BUILDING_RESOURCE_PROVIDER = 'ResourceCategory';
const FARM_PLANNER = 'FarmCategory';
const CROP_PROVIDER = 'CropCategory';
const FOOD_PROVIDER = 'FoodCategory';
const GOODS_PROVIDER = 'GoodsCategory';
const POINTS_PROVIDER = 'PointsCategory';

/*
 * Types of ressources
 */
const WOOD = 'wood';
const STONE = 'stone';
const ORE = 'ore';
const RUBY = 'ruby';
const FOOD = 'food';
const GOLD = 'gold';
const GRAIN = 'grain';
const VEGETABLE = 'vegetable';
const SHEEP = 'sheep';
const PIG = 'pig';
const CATTLE = 'cattle';
const DOG = 'dog';
const DONKEY = 'donkey';
const SCORE = 'score';
const BEGGING = 'begging';

const FIELD = 'field';

const RESOURCES = [WOOD, STONE, ORE, RUBY, FOOD, GOLD, GRAIN, VEGETABLE, SHEEP, PIG, CATTLE, DOG, DONKEY, BEGGING];
const ANIMALS = [DOG, SHEEP, PIG, CATTLE, DONKEY];
const FARM_ANIMALS = [DOG, SHEEP, PIG, CATTLE, DONKEY];
const ROOMS = ['roomStone', 'roomClay', 'roomWood'];
const CAVERN = 'Cavern';

const WEAPON = 'weapon';

const FOREST = 'forest';
const MOUNTAIN = 'mountain';

const TILE_TUNNEL_CAVERN = 'tileTunnelCavern';
const TILE_CAVERN_CAVERN = 'tileCavernCavern';
const TILE_MEADOW_FIELD = 'tileMeadowField';
const TILE_MINE_DEEP_TUNNEL = 'tileMineDeepTunnel';
const TILE_RUBY_MINE = 'tileRubyMine';
const TILE_MEADOW = 'tileMeadow';
const TILE_FIELD = 'tileField';
const TILE_CAVERN = 'tileCavern';
const TILE_TUNNEL = 'tileTunnel';
const TILE_DEEP_TUNNEL = 'tileDeepTunnel';
const TILE_ORE_MINE = 'tileOreMine';
const TILE_PASTURE = 'tilePasture';
const TILE_LARGE_PASTURE = 'tileLargePasture';

const TILE_SQUARES_MAPPING = [
  TILE_TUNNEL_CAVERN => [TILE_TUNNEL, TILE_CAVERN],
  TILE_CAVERN_CAVERN => [TILE_CAVERN, TILE_CAVERN],
  TILE_MEADOW_FIELD => [TILE_MEADOW, TILE_FIELD],
  TILE_MINE_DEEP_TUNNEL => [TILE_DEEP_TUNNEL, TILE_ORE_MINE],
  TILE_RUBY_MINE => [TILE_RUBY_MINE],
  TILE_MEADOW => [TILE_MEADOW],
  TILE_FIELD => [TILE_FIELD],
  TILE_TUNNEL => [TILE_TUNNEL],
  TILE_CAVERN => [TILE_CAVERN],
  TILE_PASTURE => [TILE_PASTURE],
  TILE_LARGE_PASTURE => [TILE_PASTURE, TILE_PASTURE],
];

// State to differentiate chah beh oui !ildren from grown ups
const ADULT = 0;
const CHILD = 1;

// Exchange triggers
const ANYTIME = 1;
const BREAD = 2;
const HARVEST = 3;

// Harvest token
const HARVEST_GREY = 'harvest_grey';
const HARVEST_NORMAL = 'harvest_normal';
const HARVEST_NONE = 'harvest_none';
const HARVEST_1FOOD = 'harvest_1food';
const HARVEST_REAP = 'harvest_reap';

/*
 * Scoring categories
 */
const SCORING_GRAINS = 'grains';
const SCORING_VEGETABLES = 'vegetables';
const SCORING_RUBIES = 'rubies';
const SCORING_DWARFS = 'dwarfs';

const SCORING_EMPTY = 'empty';
const SCORING_PASTURES = 'pastures';
const SCORING_MINES = 'mines';

const SCORING_BUILDINGS = 'buildings';
const SCORING_BUILDINGS_BONUS = 'buildingsBonus';

const SCORING_GOLD = 'gold';
const SCORING_BEGGINGS = 'beggings';
const SCORING_CATEGORIES = [
  DOG,
  SHEEP,
  PIG,
  CATTLE,
  DONKEY,

  SCORING_GRAINS,
  SCORING_VEGETABLES,

  SCORING_RUBIES,
  SCORING_DWARFS,

  SCORING_EMPTY,
  SCORING_PASTURES,
  SCORING_MINES,

  SCORING_BUILDINGS,
  SCORING_BUILDINGS_BONUS,

  SCORING_BEGGINGS,
  SCORING_GOLD,
];

const NO_COST = ['trades' => [['max' => 1]]];

/*
 * Atomic action
 */
const ACTIVATE_BUILDING = 'ACTIVATE_BUILDING';
const BLACKSMITH = 'BLACKSMITH';
const COLLECT = 'COLLECT';
const PLACE_TILE = 'PLACE_TILE';
const EXCHANGE = 'EXCHANGE';
const EXPEDITION = 'EXPEDITION';
const FIRSTPLAYER = 'FIRST_PLAYER';
const FURNISH = 'FURNISH';
const GAIN = 'GAIN';
const IMITATE = 'IMITATE';
const PAY = 'PAY';
const PLACE_DWARF = 'PLACE_DWARF';
const PLACE_FUTURE_MEEPLES = 'PLACE_FUTURE_MEEPLES';
const REAP = 'REAP';
const RECEIVE = 'RECEIVE';
const REORGANIZE = 'REORGANIZE';
const SOW = 'SOW';
const SPECIAL_EFFECT = 'SPECIAL_EFFECT';
const STABLES = 'STABLES';
const WISHCHILDREN = 'WISH_CHILDREN';
const WEAPON_INCREASE = 'WEAPON_INCREASE';
const BREED = 'BREED';

/** ExtraDatas**/
const BONUS_VP = 'bonusVP';

/******************
 ****** STATS ******
 ******************/
const STAT_POSITION = 10;
const STAT_SCORE_DOGS = 11;
const STAT_SCORE_SHEEPS = 12;
const STAT_SCORE_PIGS = 13;
const STAT_SCORE_CATTLES = 14;
const STAT_SCORE_DONKEYS = 15;
const STAT_SCORE_GRAINS = 16;
const STAT_SCORE_VEGETABLES = 17;
const STAT_SCORE_RUBIES = 18;
const STAT_SCORE_DWARFS = 19;
const STAT_SCORE_UNUSED = 20;
const STAT_SCORE_PASTURES = 21;
const STAT_SCORE_MINES = 22;
const STAT_SCORE_BUILDINGS = 23;
const STAT_SCORE_BUILDINGS_BONUS = 24;
const STAT_SCORE_BEGGINGS = 25;
const STAT_SCORE_GOLD = 26;

const STAT_FIRST_PLAYER = 30;
const STAT_PLACED_DWARF = 31;

const STAT_WOOD_FROM_BOARD = 35;
const STAT_STONE_FROM_BOARD = 36;
const STAT_ORE_FROM_BOARD = 37;
const STAT_RUBY_FROM_BOARD = 38;
const STAT_GRAIN_FROM_BOARD = 39;
const STAT_VEGETABLE_FROM_BOARD = 40;
const STAT_FOOD_FROM_BOARD = 41;
const STAT_SHEEP_FROM_BOARD = 42;
const STAT_PIG_FROM_BOARD = 43;
const STAT_CATTLE_FROM_BOARD = 44;

const STAT_WOOD_FROM_CARDS = 45;
const STAT_STONE_FROM_CARDS = 46;
const STAT_ORE_FROM_CARDS = 47;
const STAT_RUBY_FROM_CARDS = 48;
const STAT_GRAIN_FROM_CARDS = 49;
const STAT_VEGETABLE_FROM_CARDS = 50;
const STAT_FOOD_FROM_CARDS = 51;
const STAT_SHEEP_FROM_CARDS = 52;
const STAT_PIG_FROM_CARDS = 53;
const STAT_CATTLE_FROM_CARDS = 54;

const STAT_MAJOR_BUILT = 55;
const STAT_MINOR_BUILT = 56;
const STAT_OCCUPATION_BUILT = 57;
const STAT_ROOMS_BUILT = 58;

const STAT_GRAIN_CONVERTED = 59;
const STAT_VEGETABLE_CONVERTED = 60;
const STAT_SHEEP_CONVERTED = 61;
const STAT_PIG_CONVERTED = 62;
const STAT_CATTLE_CONVERTED = 63;
const STAT_STONE_CONVERTED = 64;
const STAT_WOOD_CONVERTED = 65;
const STAT_ORE_CONVERTED = 66;
const STAT_RUBY_CONVERTED = 67;
const STAT_GOLD_CONVERTED = 80;

const STAT_FOOD_FROM_GRAIN = 68;
const STAT_FOOD_FROM_VEGETABLE = 69;
const STAT_FOOD_FROM_SHEEP = 70;
const STAT_FOOD_FROM_PIG = 71;
const STAT_FOOD_FROM_CATTLE = 72;
const STAT_FOOD_FROM_STONE = 73;
const STAT_FOOD_FROM_ORE = 74;
const STAT_FOOD_FROM_RUBY = 75;
const STAT_FOOD_FROM_GOLD = 81;
const STAT_FOOD_FROM_WOOD = 76;
const STAT_BEGGING_FROM_CARDS = 77;

const STAT_HARVESTED_GRAINS = 78;
const STAT_HARVESTED_VEGETABLES = 79;
