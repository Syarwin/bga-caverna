<?php
namespace CAV\Buildings\Y;

class Y_WritingChamber extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'Y_WritingChamber';
    $this->category = 'bonus';
    $this->name = clienttranslate('Writing Chamber');
    $this->desc = [clienttranslate('When scoring, prevents loss up to 7 <SCORE>')];
    $this->tooltip = [
      clienttranslate(
        'When scoring, the Writing chamber can prevent the loss of up to 7 Gold points. You lose points for Begging markers, unused spaces on your Home board and missing Farm animals. Correction is automatically managed by BGA.'
      ),
    ];
    $this->cost = [STONE => 2];
  }

  public function computeSpecialScore($scores)
  {
    foreach ($scores as $pId => $score) {
      if ($pId != $this->getPId()) {
        continue;
      }
      $negativeEntry = 0;
      foreach ($score as $type => $values) {
        if ($type == 'total') {
          continue;
        }

        if (isset($values['entries'])) {
          foreach ($values['entries'] as $entry) {
            if ($entry['score'] < 0) {
              $negativeEntry += $entry['score'];
            }
          }
        }
      }

      $this->addBonusScoringEntry(min(7, $negativeEntry * -1));
    }
  }
}
