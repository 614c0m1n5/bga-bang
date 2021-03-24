<?php

class ChuckWengam  extends \BANG\Models\Player{
  public function __construct($row = null)
  {
    $this->character    = CHUCK_WENGAM;
    $this->character_name = clienttranslate('Chuck Wengam');
    $this->text  = [
      clienttranslate("During his turn, he may choose to lose 1 life point to draw 2 cards "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
    parent::__construct($row);
  }
}