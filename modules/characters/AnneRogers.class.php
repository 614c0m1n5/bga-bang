<?php

class AnneRogers extends BangPlayer {
  public function __construct($row = null)
  {
    $this->character    = ANNE_ROGERS;
    $this->character_name = clienttranslate('Anne Rogers');
    $this->text  = [
      clienttranslate("Can select blue card to copy its benefit until next turn"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;
    parent::__construct($row);
  }
}