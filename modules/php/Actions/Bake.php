<?php
namespace CAV\Actions;

class Bake extends \CAV\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = clienttranslate('Bake');
  }

  public function getState()
  {
    return ST_BAKE;
  }

  public function argsBake()
  {
    return [];
  }
}
