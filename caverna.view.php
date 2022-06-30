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
 * caverna.view.php
 *
 */

require_once APP_BASE_PATH . 'view/common/game.view.php';

class view_caverna_caverna extends game_view
{
  function getGameName()
  {
    return 'caverna';
  }
  function build_page($viewArgs)
  {
    $this->tpl['YOUR_HAND'] = self::_("Your hand");
    $this->tpl['DRAFT'] = self::_("Draft pool");

  }
}
