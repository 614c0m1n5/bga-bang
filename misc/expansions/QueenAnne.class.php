<?php

class QueenAnne  extends \BANG\Models\Player{
  public function __construct($row = null)
  {
    $this->character    = QUEEN_ANNE;
    $this->character_name = clienttranslate('Queen Anne');
    $this->text  = [
      clienttranslate("Discard card from a player seen within distance 2"),

    ];
    $this->bullets = 4;
    $this->expansion = ROBBERTS_ROOST;  
    parent::__construct($row);
  }
}