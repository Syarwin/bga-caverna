<?php
namespace CAV\Buildings\D;

use CAV\Core\Notifications;
use CAV\Managers\Dwarfs;

class D_AddDwelling extends \CAV\Models\Building
{
  public function __construct($row)
  {
    parent::__construct($row);
    $this->type = 'D_AddDwelling';
    $this->category = 'dwelling';
    $this->name = clienttranslate('Additional Dwelling');
    $this->desc = [clienttranslate('room for the sixth dwarf only')];
    $this->tooltip = [
      clienttranslate(
        'The Additional dwelling provides room for a sixth Dwarf. Until you do not have 6 Dwarfs, this Dwelling stays empty.'
      ),
      clienttranslate('You can build it even if you do not have 5 Dwarfs at the time you build it.'),
      clienttranslate(
        'Once you have 5 Dwarfs, you can use a Family growth action to get a sixth Dwarf (see also “Family chamber”).'
      ),
    ];
    $this->cost = [WOOD => 4, STONE => 3];
    $this->vp = 5;
  }

  public function getDwelling()
  {
    // provides room only if 5 others dwarfs are placed
    if (
      $this->getPlayer()
        ->getAllDwarfs()
        ->count() < 5
    ) {
      return 0;
    }
    return 1;
  }

  public function onBuy($player, $eventData)
  {
    $created = Dwarfs::singleCreate([
      'type' => 'dwarf',
      'player_id' => $player->getId(),
      'location' => 'reserve',
      'nbr' => 1,
    ]);
    Notifications::gainDwarf($player, [$created], $this->name);
  }
}
