<?php

class RoseDoolan extends BangPlayer {
  public function __construct($row = null)
  {
    parent::__construct($row);
    $this->character    = ROSE_DOOLAN;
    $this->character_name = clienttranslate('Rose Doolan');
    $this->text  = [
      clienttranslate("She sees all players at distance -1"),

    ];
    $this->bullets = 4;  
  }
}