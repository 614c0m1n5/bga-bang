<?php

class JohnnyPope extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = JOHNNY_POPE;
    $this->name  = clienttranslate('Johnny Pope');
    $this->text  = [
      clienttranslate("Gives LP to other player OR makes player “draw!” Reds=steal card from player; Blacks=player must discard 2 missed or take hit."),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
  }
}