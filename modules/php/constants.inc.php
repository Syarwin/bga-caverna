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
const ST_FENCING = 22;
const ST_PAY = 23;
const ST_COLLECT = 24;
const ST_FIRSTPLAYER = 25;
const ST_OCCUPATION = 26;
const ST_PLOW = 27;
const ST_PLACE_TILE = 28;
const ST_SOW = 29;
const ST_STABLE = 30;
const ST_RENOVATION = 31;
const ST_IMPROVEMENT = 32;
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
const ANIMALS = [SHEEP, PIG, CATTLE];
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

const TILE_SQUARES_MAPPING = [
  TILE_TUNNEL_CAVERN => [TILE_TUNNEL, TILE_CAVERN],
  TILE_CAVERN_CAVERN => [TILE_CAVERN, TILE_CAVERN],
  TILE_MEADOW_FIELD => [TILE_MEADOW, TILE_FIELD],
  TILE_MINE_DEEP_TUNNEL => [], // TODO
  TILE_RUBY_MINE => [TILE_RUBY_MINE],
  TILE_MEADOW => [TILE_MEADOW],
  TILE_FIELD => [TILE_FIELD],
];

// State to differentiate chah beh oui !ildren from grown ups
const ADULT = 0;
const CHILD = 1;

// Exchange triggers
const ANYTIME = 1;
const BREAD = 2;
const HARVEST = 3;

/*
 * Scoring categories
 */
const SCORING_FIELDS = 'fields';
const SCORING_PASTURES = 'pastures';
const SCORING_GRAINS = 'grains';
const SCORING_VEGETABLES = 'vegetables';
const SCORING_SHEEPS = 'sheeps';
const SCORING_PIGS = 'pigs';
const SCORING_CATTLES = 'cattles';
const SCORING_EMPTY = 'empty';
const SCORING_STABLES = 'stables';
const SCORING_CLAY_ROOMS = 'clayRooms';
const SCORING_STONE_ROOMS = 'stoneRooms';
const SCORING_FARMERS = 'farmers';
const SCORING_CARDS = 'cards';
const SCORING_CARDS_BONUS = 'cardsBonus';
const SCORING_BEGGINGS = 'beggings';
const SCORING_CATEGORIES = [
  SCORING_FIELDS,
  SCORING_PASTURES,
  SCORING_GRAINS,
  SCORING_VEGETABLES,
  SCORING_SHEEPS,
  SCORING_PIGS,
  SCORING_CATTLES,
  SCORING_EMPTY,
  SCORING_STABLES,
  SCORING_CLAY_ROOMS,
  SCORING_STONE_ROOMS,
  SCORING_FARMERS,
  SCORING_CARDS,
  SCORING_CARDS_BONUS,
  SCORING_BEGGINGS,
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
const FENCING = 'FENCING';
const FIRSTPLAYER = 'FIRST_PLAYER';
const FURNISH = 'FURNISH';
const GAIN = 'GAIN';
const IMITATE = 'IMITATE';
const PAY = 'PAY';
const PLACE_DWARF = 'PLACE_DWARF';
const PLACE_FUTURE_MEEPLES = 'PLACE_FUTURE_MEEPLES';
const PLOW = 'PLOW';
const REAP = 'REAP';
const RECEIVE = 'RECEIVE';
const REORGANIZE = 'REORGANIZE';
const SOW = 'SOW';
const SPECIAL_EFFECT = 'SPECIAL_EFFECT';
const STABLES = 'STABLES';
const WISHCHILDREN = 'WISH_CHILDREN';
const WEAPON_INCREASE = 'WEAPON_INCREASE';

/** ExtraDatas**/
const BONUS_VP = 'bonusVP';

/******************
 ****** STATS ******
 ******************/
const STAT_POSITION = 10;
const STAT_SCORE_FIELDS = 11;
const STAT_SCORE_PASTURES = 12;
const STAT_SCORE_GRAINS = 13;
const STAT_SCORE_VEGETABLES = 14;
const STAT_SCORE_SHEEPS = 15;
const STAT_SCORE_PIGS = 16;
const STAT_SCORE_CATTLES = 17;
const STAT_SCORE_UNUSED = 18;
const STAT_SCORE_STABLES = 19;
const STAT_SCORE_CLAY_ROOMS = 20;
const STAT_SCORE_STONE_ROOMS = 21;
const STAT_SCORE_FARMERS = 22;
const STAT_SCORE_BEGGINGS = 23;
const STAT_SCORE_CARDS = 24;
const STAT_SCORE_CARDS_BONUS = 25;

const STAT_FIRST_PLAYER = 30;
const STAT_PLACED_FARMER = 31;

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

const STAT_FOOD_FROM_GRAIN = 68;
const STAT_FOOD_FROM_VEGETABLE = 69;
const STAT_FOOD_FROM_SHEEP = 70;
const STAT_FOOD_FROM_PIG = 71;
const STAT_FOOD_FROM_CATTLE = 72;
const STAT_FOOD_FROM_STONE = 73;
const STAT_FOOD_FROM_ORE = 74;
const STAT_FOOD_FROM_RUBY = 75;
const STAT_FOOD_FROM_WOOD = 76;
const STAT_BEGGING_FROM_CARDS = 77;

const STAT_HARVESTED_GRAINS = 78;
const STAT_HARVESTED_VEGETABLES = 79;
