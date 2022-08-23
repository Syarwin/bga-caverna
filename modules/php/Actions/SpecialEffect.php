<?php
namespace CAV\Actions;
use CAV\Managers\Buildings;

class SpecialEffect extends \CAV\Models\Action
{
  public function getState()
  {
    return ST_SPECIAL_EFFECT;
  }

  public function isDoable($player, $ignoreResources = false)
  {
    $args = $this->getCtxArgs();
    if (isset($args['cardId'])) {
      $card = Buildings::get($args['cardId']);
    } elseif (isset($args['cardType'])) {
      $card = Buildings::getFilteredQuery(null, null, $args['cardType'])
        ->get()
        ->first();
    } else {
      throw new \feException('Issue on special effect args');
    }
    $method = 'is' . \ucfirst($args['method']) . 'Doable';
    $arguments = $args['args'] ?? [];

    return \method_exists($card, $method) ? $card->$method($player, $ignoreResources, ...$arguments) : true;
  }

  public function getDescription($ignoreResources = false)
  {
    $args = $this->getCtxArgs();
    if (isset($args['cardId'])) {
      $card = Buildings::get($args['cardId']);
    } elseif (isset($args['cardType'])) {
      $card = Buildings::getFilteredQuery(null, null, $args['cardType'])
        ->get()
        ->first();
    } else {
      throw new \feException('Issue on special effect args');
    }
    $method = 'get' . \ucfirst($args['method']) . 'Description';
    $arguments = $args['args'] ?? [];
    return \method_exists($card, $method) ? $card->$method(...$arguments) : '';
  }

  public function isIndependent($player = null)
  {
    $args = $this->getCtxArgs();
    if (isset($args['cardId'])) {
      $card = Buildings::get($args['cardId']);
    } elseif (isset($args['cardType'])) {
      $card = Buildings::getFilteredQuery(null, null, $args['cardType'])
        ->get()
        ->first();
    } else {
      throw new \feException('Issue on special effect args');
    }
    $method = 'isIndependent' . \ucfirst($args['method']);
    return \method_exists($card, $method) ? $card->$method($player) : false;
  }

  public function isAutomatic($player = null)
  {
    $args = $this->getCtxArgs();
    if (isset($args['cardId'])) {
      $card = Buildings::get($args['cardId']);
    } elseif (isset($args['cardType'])) {
      $card = Buildings::getFilteredQuery(null, null, $args['cardType'])
        ->get()
        ->first();
    } else {
      throw new \feException('Issue on special effect args');
    }
    $method = $args['method'];
    return \method_exists($card, $method);
  }

  public function stSpecialEffect()
  {
    $args = $this->getCtxArgs();
    if (isset($args['cardId'])) {
      $card = Buildings::get($args['cardId']);
    } elseif (isset($args['cardType'])) {
      $card = Buildings::getFilteredQuery(null, null, $args['cardType'])
        ->get()
        ->first();
    } else {
      throw new \feException('Issue on special effect args');
    }
    $method = $args['method'];
    $arguments = $args['args'] ?? [];
    if (\method_exists($card, $method)) {
      $card->$method(...$arguments);
      $this->resolveAction();
    }
  }

  public function argsSpecialEffect()
  {
    $args = $this->getCtxArgs();
    if (isset($args['cardId'])) {
      $card = Buildings::get($args['cardId']);
    } elseif (isset($args['cardType'])) {
      $card = Buildings::getFilteredQuery(null, null, $args['cardType'])
        ->get()
        ->first();
    } else {
      throw new \feException('Issue on special effect args');
    }
    $method = 'args' . \ucfirst($args['method']);
    $arguments = $args['args'] ?? [];
    return \method_exists($card, $method) ? $card->$method(...$arguments) : [];
  }

  public function actSpecialEffect(...$actArgs)
  {
    $args = $this->getCtxArgs();
    if (isset($args['cardId'])) {
      $card = Buildings::get($args['cardId']);
    } elseif (isset($args['cardType'])) {
      $card = Buildings::getFilteredQuery(null, null, $args['cardType'])
        ->get()
        ->first();
    } else {
      throw new \feException('Issue on special effect args');
    }
    $method = 'act' . \ucfirst($args['method']);
    $arguments = $args['args'] ?? [];
    if (!\method_exists($card, $method)) {
      throw new BgaVisibleSystemException('Corresponding act function does not exists : ' . $method);
    }

    $card->$method(...array_merge($actArgs, $arguments));
    $this->resolveAction();
  }
}
