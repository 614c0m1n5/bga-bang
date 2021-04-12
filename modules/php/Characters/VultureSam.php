<?php
namespace BANG\Characters;
use BANG\Core\Notifications;
use BANG\Managers\Cards;

class VultureSam extends \BANG\Models\Player
{
  public function __construct($row = null)
  {
    $this->character = VULTURE_SAM;
    $this->character_name = clienttranslate('Vulture Sam');
    $this->text = [
      clienttranslate('Whenever a character is eliminated from the play, he takes all the cards of that player.'),
    ];
    $this->bullets = 4;
    parent::__construct($row);
  }

  public function onPlayerEliminated($player)
  {
    //$cards = array_merge($player->getCardsInHand(), $player->getCardsInPlay());
    // TODO send a single notification?
    foreach ($player->getCardsInHand() as $card) {
      Cards::moveCard($card, LOCATION_HAND, $this->id);
      Notifications::stoleCard($this, $player, $card, false);
    }
    foreach ($player->getCardsInPlay() as $card) {
      Cards::moveCard($card, LOCATION_HAND, $this->id);
      Notifications::stoleCard($this, $player, $card, true);
    }
  }
}
