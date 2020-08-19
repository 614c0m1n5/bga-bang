<?php

/*
 * BangPlayer: all utility functions concerning a player
 */
class BangPlayer extends APP_GameClass
{
  protected $id;
  protected $no; // natural order
  protected $name; // player name
  protected $color;
  protected $eliminated = false;
  protected $hp;
  protected $zombie = false;
  protected $role;
  // --character properties
	protected $character; //the int-constant
  protected $character_name;
	protected $text;
	protected $bullets;
  protected $expansion = BASE_GAME;


  public function __construct($row) {
    if($row != null) {
      $this->id = $row['player_id'];
      $this->no = (int)$row['player_no'];
      $this->name = $row['player_name'];
      $this->color = $row['player_color'];
      $this->eliminated = $row['player_eliminated'] == 1;
      $this->hp = (int)$row['player_score'];
      $this->zombie = $row['player_zombie'] == 1;
      $this->role = $row['player_role'];
      $this->bullets = (int)$row['player_bullets'];
    }
  }

  /*
   * Getters
   */
  public function getId(){ return $this->id; }
  public function getNo(){ return $this->no; }
  public function getName(){ return $this->name; }
  public function getColor(){ return $this->color; }
  public function getHp(){ return $this->hp; }
  public function getRole(){ return $this->role; }
  public function getCharName() { return $this->character_name; }
  public function isEliminated(){ return $this->eliminated; }
  public function isZombie(){ return $this->zombie; }

  public function getPosition(){ return BangPlayerManager::getPlayerPositions()[$this->id]; }
  public function getText() { return $this->text;}
  public function getExpansion() { return $this->expansion;}
  public function getBullets() { return $this->bullets;}
  public function getCardsInHand($formatted = false){ return BangCardManager::getHand($this->id, $formatted); }
  public function getCardsInPlay(){ return BangCardManager::getCardsInPlay($this->id); }
  public function countCardsInHand() { return BangCardManager::countCards("hand", $this->id);}


  public function getUiData($currentPlayerId = null) {
    $current = $this->id == $currentPlayerId;
    return [
      'id'        => $this->id,
      'no'        => $this->no,
      'name'      => $this->getName(),
      'color'     => $this->color,
      'characterId' => $this->character,
      'character'   => $this->character_name,
      'powers'      => $this->text,
      'hp'          => $this->hp,
      'bullets'     => $this->bullets,
      'hand' => ($current) ? array_values(BangCardManager::getHand($this->id, true)) : BangCardManager::countCards('hand', $this->id),
      'role' => ($current || $this->role==SHERIFF) ? $this->role : null,
      'inPlay' => array_values(BangCardManager::getCardsInPlay($this->id, true)),
    ];
  }


  /**
   * saves eliminated status and hp to the database
   */
  public function save() {
    $eliminated = ($this->eliminated) ? 1 : 0;
    $sql = "UPDATE player SET player_eliminated=$eliminated, player_score= " . $this->hp . " WHERE player_id=" . $this->id;
    self::DbQuery($sql);
  }


  public function useAbility($args) {}



/*************************
********** Utils *********
*************************/

  /*
   * Draw $amount card from deck and notify them
   */
  public function drawCards($amount){
    $cards = BangCardManager::deal($this->id, $amount);
    BangNotificationManager::gainedCards($this, $cards);
  }

  /*
   * Discard a card and notify (with/without a message) it
   */
  public function discardCard($card, $silent = false){
    BangCardManager::discardCard($card);
    BangNotificationManager::discardedCard($this, $card, $silent);
  }

  /*
   * Discard its weapon
   */
  public function discardWeapon(){
    $weapon = $this->getWeapon();
    if(!is_null($weapon))
      $this->discardCard($weapon, true);
  }


  /*
   * Draw! (careful, not the same as drawCard), notify and return the card
   */
  public function draw($args, $src) {
    $card = BangCardManager::draw();
    BangNotificationManager::drawCard($this, $card, $src);
    return $card;
  }


  /**
   * incresase the life points of a player.
   */
  public function gainLife($amount = 1) {
    if($this->hp == $this->bullets) return;

    $this->hp += $amount;
    if($this->hp > $this->bullets) $this->hp = $this->bullets;
    $this->save();
    BangNotificationManager::gainedLife($this, $amount);
	}

  /**
   * reduces the life points of a player by 1.
   * return: whether the player was eliminated
   */
  public function looseLife($byPlayer = null, $amount = 1) {
		$this->hp -= $amount;
    if($this->hp < 0) $this->hp = 0;
		$this->save();
    BangNotificationManager::lostLife($this);
    if($this->hp == 0) {
      $byPlayer = $byPlayer ?? BangPlayerManager::getCurrentTurn();
      $this->eliminate($byPlayer);
      return true;
    }
    return false;
	}


/************************************
********** Advanced getters *********
************************************/

  /*
   * Return a random card from the hand (useful for drawing in hand for instance)
   */
  public function getRandomCardInHand(){
    $cards = self::getCardsInHand();
    if(empty($cards)){
      throw new BgaVisibleSystemException("Cannot draw a card in an empty hand");
    }
    shuffle($cards);
    return $cards[0];
  }


  /**
   * returns the current distance to an enmy from the view of the enemy
   * should not be called on the player checking for targets but on the other players
   */
  public function getDistanceTo($enemy) {
    $positions = BangPlayerManager::getPlayerPositions();
    $pos1 = $positions[$this->getId()];
    $pos2 = $positions[$enemy->getId()];
    $d = abs($pos2 - $pos1);
    $dist = min($d, count($positions) - $d);
    foreach($enemy->getCardsInPlay() as $card) {
      if($card->getEffect()['type'] == RANGE_DECREASE) $dist--;
    }
    foreach($this->getCardsInPlay() as $card) {
      if($card->getEffect()['type'] == RANGE_INCREASE) $dist++;
    }
    return $dist;
  }


  public function isInRange($enemy, $range){
    return ($enemy->getDistanceTo($this) <= $range);
  }


  /**
  * getPlayerInRange : Returns the players ids in range of weapon
  */
  public function getPlayersInRange($range) {
		$targets = BangPlayerManager::getPlayers();

    Utils::filter($targets, function($player) use ($range){
      return ($player->getDistanceTo($this) <= $range); // TODO : use isInRange => weird bug...
    });

    return array_map(function($target){ return $target->getId(); }, $targets);
	}


  /**
  * getWeapon : Returns weapon card of player, or null if not equipped
  */
  public function getWeapon(){
    return array_reduce($this->getCardsInPlay(), function($weapon, $card){
      $effect = $card->getEffect();
      return ($effect['type'] == WEAPON) ? $card : $weapon;
    }, null);
  }

  /**
  * getRange : Returns the range of player's weapon
  */
  public function getRange() {
    $weapon = $this->getWeapon();
    return is_null($weapon)? 1 : $weapon->getEffect()['range'];
  }


  public function hasUnlimitedBangs() {
    $weapon = $this->getWeapon();
    return !is_null($weapon) && $weapon->getType() == CARD_VOLCANIC;
  }


  public function hasPlayedBang(){
    return !is_null(BangLog::getLastAction("bangPlayed", $this->id));
  }


  /*
   * return the list of bang cards (for indians and duel for instance)
   */
  public function getBangCards() {
    $hand = $this->getCardsInHand();
    Utils::filter($hand, function($card){
      return $card->getType() == CARD_BANG;
    });
    $cards = array_map(function($card){
      return [
        'id' => $card->getId(),
        'options' => ['type' => OPTION_NONE],
      ];
    }, $hand);

    return [
      'cards' => $cards,
      'character' => null
    ];
  }


  /*
   * return defensive options
   */
  public function getDefensiveOptions() {
    // Defensive cards in hand
    $hand = $this->getCardsInHand();
    Utils::filter($hand, function($card){ return $card->getColor() == BROWN && $card->getEffectType() == DEFENSIVE; });
    $res = array_map(function($card){ return ['id' => $card->getId(), 'options' => ['type' => OPTION_NONE] ]; }, $hand);

    // Defensive cards in play
    $card = array_reduce($this->getCardsInPlay(), function($barrel, $card){
      return ($card->getType() == CARD_BARREL && !$card->wasPlayed()) ? $card : $barrel;
    }, null);
    if(!is_null($card)) $res[] = ['id' => $card->getId(), 'options' => ['type' => OPTION_NONE]];

    return ['cards' => array_values($res), 'character' => null];
  }



/***************************************
****************************************
**************** Actions ***************
****************************************
***************************************/

  /**
   * startOfTurn: is called at the beggining of each turn (before the drawing phase)
   *   return: the new state it should continue with
   */
  public function startOfTurn() {
    $equipment = $this->getCardsInPlay();
		// make sure dynamite gets handled before jail
		Utils::sort($equipment, function($a, $b) { return $a->getType() > $b->getType();});
    return array_reduce($equipment, function($state, $card){
      if($state == 'skip' || $card->getEffectType() != STARTOFTURN) return $state;
      $newState = $card->activate($this);
      return $newState ?: $state;
    }, 'draw');
  }


  /**
   * getHandOptions: give the list of playable cards in hand, along with their options
   */
  public function getHandOptions() {
    $options = array_map(function($card){
      return [
        'id' => $card->getId(),
        'options' => $card->getPlayOptions($this)
      ];
    }, $this->getCardsInHand() );
    Utils::filter($options, function($card) { return !is_null($card['options']); });
    return [
      'cards' => array_values($options),
      'character' => null
    ];
  }


  /**
   * playCard: play a card given by id with args to specify the chosen option
   */
  public function playCard($id, $args) {
		$card = BangCardManager::getCard($id);
    BangNotificationManager::cardPlayed($this, $card, $args);
    BangLog::addCardPlayed($this, $card, $args);
    return $card->play($this, $args);
	}


  /**
   * react: whenever a player react by passing or playing a card
   */
	public function react($id) {
		$card = BangCardManager::getCurrentCard();
    if($id == PASS)
      return $card->pass($this);
    else {
      $reactionCard = BangCardManager::getCard($id);
      return $card->react($reactionCard, $this);
    }
	}


  /**
   * attack : performs an attack on all given players
   */
  public function attack($playerIds, $checkBarrel = true) {
    $reactions = [];
    foreach(BangPlayerManager::getPlayers($playerIds) as $player){
      // Player has defensive equipment ? (eg Barrel)
      $canUseEquipment = false;
      if($checkBarrel) {
        $canUseEquipment = array_reduce($player->getCardsInPlay(), function($hasDefense, $card){
          return $hasDefense || ($card->getEffectType() == DEFENSIVE);
        }, false);
      }

      // Otherwise, player has at least one card in hand ?
      if($player->countCardsInHand() > 0 || $canUseEquipment)  {
  			$reactions[] = $player->id; // Give him a chance to (pretend to) react
  		} else {
  			$player->looseLife($this); // Lost life immediatly
  		}
    }

    // Go to corresponding state
    if(count($reactions) == 1) {
      BangLog::addAction("target", ["target" => $reactions[0]]);
      return "react";
    } elseif(count($reactions) > 1) {
      BangLog::addAction("target", ["target" => $reactions]);
      return "multiReact";
    }
    return null;
  }






  public function eliminate($byPlayer = null){
    $this->eliminated = true;
  }
}
