<?php
/**
 *------
 * BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
 * caverna implementation : © Timothée Pecatte <tim.pecatte@gmail.com>, Vincent Toper <vincent.toper@gmail.com>
 *
 * This code has been produced on the BGA studio platform for use on https://boardgamearena.com.
 * See http://en.doc.boardgamearena.com/Studio for more information.
 * -----
 *
 * caverna.action.php
 *
 * caverna main action entry point
 *
 */

class action_caverna extends APP_GameAction
{
  // Constructor: please do not modify
  public function __default()
  {
    if (self::isArg('notifwindow')) {
      $this->view = 'common_notifwindow';
      $this->viewArgs['table'] = self::getArg('table', AT_posint, true);
    } else {
      $this->view = 'caverna_caverna';
      self::trace('Complete reinitialization of board game');
    }
  }

  public function loadBugSQL()
  {
    self::setAjaxMode();
    $reportId = (int) self::getArg('report_id', AT_int, true);
    $this->game->loadBugSQL($reportId);
    self::ajaxResponse();
  }

  public function actLoadSeed()
  {
    self::setAjaxMode();
    $seed = self::getArg('seed', AT_json, false);
    $this->game->actLoadSeed($seed);
    self::ajaxResponse();
  }

  public function actChangePref()
  {
    self::setAjaxMode();
    $pref = self::getArg('pref', AT_posint, false);
    $value = self::getArg('value', AT_posint, false);
    $this->game->actChangePreference($pref, $value);
    self::ajaxResponse();
  }

  public function actConfirmTurn()
  {
    self::setAjaxMode();
    $this->game->actConfirmTurn();
    self::ajaxResponse();
  }

  public function actConfirmPartialTurn()
  {
    self::setAjaxMode();
    $this->game->actConfirmPartialTurn();
    self::ajaxResponse();
  }

  public function actRestart()
  {
    self::setAjaxMode();
    $this->game->actRestart();
    self::ajaxResponse();
  }

  public function actDraftAdd()
  {
    self::setAjaxMode();
    $cId = self::getArg('cardId', AT_alphanum, true);
    $result = $this->game->actDraftAdd($cId);
    self::ajaxResponse();
  }
  public function actDraftRemove()
  {
    self::setAjaxMode();
    $cId = self::getArg('cardId', AT_alphanum, true);
    $result = $this->game->actDraftRemove($cId);
    self::ajaxResponse();
  }
  public function actDraftConfirm()
  {
    self::setAjaxMode();
    $result = $this->game->actDraftConfirm();
    self::ajaxResponse();
  }

  public function actOrderCards()
  {
    $ids = self::getArg('cardIds', AT_json, true);
    $this->validateJSonAlphaNum($ids, 'cardIds');
    $this->game->actOrderCards($ids);
    self::ajaxResponse();
  }

  public function actPlaceFarmer()
  {
    self::setAjaxMode();
    $cId = self::getArg('cId', AT_alphanum, true);
    $result = $this->game->actPlaceFarmer($cId);
    self::ajaxResponse();
  }

  public function actDraftCards()
  {
    self::setAjaxMode();
    $cId = self::getArg('cId', AT_alphanum, true);
    $cId2 = self::getArg('cId2', AT_alphanum, false);
    $result = $this->game->actDraftCards([$cId, $cId2]);
    self::ajaxResponse();
  }

  public function actTakeAtomicAction()
  {
    self::setAjaxMode();
    $args = self::getArg('actionArgs', AT_json, true);
    $this->validateJSonAlphaNum($args, 'actionArgs');
    $this->game->actTakeAtomicAction($args);
    self::ajaxResponse();
  }

  public function actChooseAction()
  {
    self::setAjaxMode();
    $choiceId = self::getArg('id', AT_int, true);
    $result = $this->game->actChooseAction($choiceId);
    self::ajaxResponse();
  }

  public function actPassOptionalAction()
  {
    self::setAjaxMode();
    $result = $this->game->actPassOptionalAction();
    self::ajaxResponse();
  }

  public function actAnytimeAction()
  {
    self::setAjaxMode();
    $choiceId = self::getArg('id', AT_int, true);
    $result = $this->game->actAnytimeAction($choiceId);
    self::ajaxResponse();
  }

  //////////////////
  ///// UTILS  /////
  //////////////////
  public function validateJSonAlphaNum($value, $argName = 'unknown')
  {
    if (is_array($value)) {
      foreach ($value as $key => $v) {
        $this->validateJSonAlphaNum($key, $argName);
        $this->validateJSonAlphaNum($v, $argName);
      }
      return true;
    }
    if (is_int($value)) {
      return true;
    }
    $bValid = preg_match("/^[_0-9a-zA-Z- ]*$/", $value) === 1;
    if (!$bValid) {
      throw new feException("Bad value for: $argName", true, true, FEX_bad_input_argument);
    }
    return true;
  }
}
