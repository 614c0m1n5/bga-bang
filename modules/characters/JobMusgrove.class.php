<?php

class JobMusgrove extends BangCharacter {
  public function __construct($game, $playerId)
  {
    parent::__construct($game, $playerId);
    $this->id    = JOB_MUSGROVE;
    $this->name  = clienttranslate('Job Musgrove');
    $this->text  = [
      clienttranslate("May “draw!” Royals=discard card from attacker's hand"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
  }
}