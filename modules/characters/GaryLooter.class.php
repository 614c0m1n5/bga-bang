<?php

class GaryLooter extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = GARY_LOOTER;
    $this->character_name = clienttranslate('Gary Looter');
    $this->text  = [
      clienttranslate("He draws all excess cards discarded by other players at the end of their turn. "),

    ];
    $this->bullets = 5;
    $this->expansion = WILD_WEST_SHOW;  
    parent::__construct($row);
  }
}