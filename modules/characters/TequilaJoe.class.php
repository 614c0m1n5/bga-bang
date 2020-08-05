<?php

class TequilaJoe extends BangPlayer {
  public function __construct($row = null)
  {
    parent::__construct($row);
    $this->character    = TEQUILA_JOE;
    $this->character_name = clienttranslate('Tequila Joe');
    $this->text  = [
      clienttranslate("Each time he plays a Beer, he regains 2 life points instead of 1. "),

    ];
    $this->bullets = 4;
    $this->expansion = DODGE_CITY;  
  }
}