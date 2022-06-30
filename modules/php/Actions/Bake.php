<?php
namespace AGR\Actions;

class Bake extends \AGR\Models\Action
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
