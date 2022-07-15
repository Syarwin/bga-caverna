
-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- caverna implementation : © Timothée Pecatte <tim.pecatte@gmail.com>, Vincent Toper <vincent.toper@gmail.com>
--
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql


CREATE TABLE IF NOT EXISTS `meeples` (
  `meeple_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `meeple_location` varchar(32) NOT NULL,
  `meeple_state` int(10),
  `type` varchar(32),
  `player_id` int(10) NULL,
  `x` varchar(100) NULL,
  `y` varchar(100) NULL,
  PRIMARY KEY (`meeple_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `global_variables` (
  `name` varchar(255) NOT NULL,
  `value` JSON,
  PRIMARY KEY (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `cards` (
  `card_id` varchar(100)  NOT NULL,
  `card_location` varchar(32) NOT NULL,
  `card_state` int(10) DEFAULT 0,
  `extra_datas` JSON NULL,
  PRIMARY KEY (`card_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `buildings` (
  `building_id` varchar(100)  NOT NULL,
  `building_location` varchar(32) NOT NULL,
  `building_state` int(10) DEFAULT 0,
  `player_id` int(10) DEFAULT 0,
  `extra_datas` JSON NULL,
  PRIMARY KEY (`building_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `user_preferences` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `player_id` int(10) NOT NULL,
  `pref_id` int(10) NOT NULL,
  `pref_value` int(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


CREATE TABLE IF NOT EXISTS `log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `move_id` int(10) NOT NULL,
  `table` varchar(32) NOT NULL,
  `primary` varchar(32) NOT NULL,
  `type` varchar(32) NOT NULL,
  `affected` JSON,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `gamelog` ADD `cancel` TINYINT(1) NOT NULL DEFAULT 0;
