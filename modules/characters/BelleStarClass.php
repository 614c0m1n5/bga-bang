<?php

class BelleStar extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = BELLE_STAR;
    $this->name  = clienttranslate('Belle Star');
    $this->text  = [
      clienttranslate("During her turn, cards in play in front of other players have no effect. "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
  }
}