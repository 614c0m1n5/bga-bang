<?php

class CardMissed extends BangCard {
  public function __construct($id=null, $game=null)
  {
    parent::__construct($id, $game);
    $this->type    = CARD_MISSED;
    $this->name  = clienttranslate('Missed');
    $this->text  = "Discard to avoid an attack";
    $this->color = BROWN; //BROWN, BLUE, GREEN
    $this->effect = ['type' => DEFENSIVE, // BASIC_ATTACK, DRAW, DEFENSIVE, DISCARD, LIFE_POINT_MODIFIER, RANGE_INCREASE, RANGE_DECREASE, OTHER
					]; 
    

    
    $this->copies = [
      BASE_GAME => [ '10C','JC','QC','KC','AC','2S','3S','4S','5S','6S','7S','8S' ],
      DODGE_CITY => [ ],
    ];
  }
}