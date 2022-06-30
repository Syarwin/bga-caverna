<?php
namespace AGR\Actions;

class MinorImprovement extends \AGR\Models\Action
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->description = clienttranslate('Play an occupation');
  }

  public function getState()
  {
    return ST_MINORIMPROVEMENT;
  }

  public function argsMinorImprovement()
  {
    // TODO: return all minor improvements that can be played
    return [];
  }

  public function actMinorImprovement()
  {
  }
}
