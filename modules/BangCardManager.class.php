<?php

/*
 * BangCardManager: all utility functions concerning cards are here
 */
class BangCardManager extends APP_GameClass
{
	public $game;
	public static $deck = null;

	public function __construct($game)	{
		$this->game = $game;
	}

	public function setupNewGame($expansions)
	{
		$cards = [];
		foreach(self::$classes as $id => $name) {
			$card = new $name();
			foreach($expansions as $exp) {
				foreach($card->getCopies()[$exp] as $value) {
					$cards[] = ['type' => $value, 'type_arg' => $id, 'nbr' => 1];
				}
			}
		}
		self::getDeck()->createCards($cards, 'deck');
		self::getDeck()->shuffle('deck');
	}

	private static function getDeck() {
		if(self::$deck==null) {
				self::$deck = self::getNew("module.common.deck");
				self::$deck->init("card");
				self::$deck->autoreshuffle = true;
		}
		return self::$deck;
	}

	/**
	 * getDeckCount : Returns the number of cards in the Deck
	 */
	public static function countCards($location, $player=null) {
		if($player==null)
			return self::getDeck()->countCardsInLocation($location);
		else
			return self::getDeck()->countCardsInLocation($location, $player);
	}



	/**
	  * getHand : Returns the cards of a players hand
	  */
	public static function getHand($id) {
		return self::getDeck()->getCardsInLocation('hand', $id);
	}

	/**
	 * getCardsInPlay : returns all Cards in play
	 */
	public static function getCardsInPlay($player_id = null) {
		if($player_id == null)
				return self::getDeck()->getCardsInLocation('inPlay');
		return self::getDeck()->getCardsInLocation('inPlay', $player_id);
	}

	/**
	 * getEquipment : returns all equipment Cards the players has in play as array: id => cards
	 */
	public static function getEquipment() {
		$cards = [];
		$bplayers = BangPlayerManager::getPlayers();
		foreach($bplayers as $id => $char) {
			$cards[$id] = self::getCardsInLocation('inPlay');
		}
		return $cards;
	}

	/*
	 *
	 */
	public static function getCard($id, $game=null) {
		$c = self::getDeck()->getCard($id);
		$card_id = $c['card_type_arg'];
		$name = self::$classes[$card_id];
		$card = new $name($id, $game);
		$card->setCopy($c['card_type']);
		return $card;
	}


	public static function moveCard($id, $location, $arg=0) {
		self::getDeck()->moveCard($id, $location, $arg);
	}

	public static function deal($player, $amount){
		self::getDeck()->pickCards($amount, 'deck', $player);
	}

	/*
	 * cardClasses : for each card Id, the corresponding class name
	 */
	public static $classes = [
		CARD_SCHOFIELD => 'CardSchofield',
		CARD_VOLCANIC => 'CardVolcanic',
		CARD_REMINGTON => 'CardRemington',
		CARD_REV_CARABINE => 'CardRevCarabine',
		CARD_WINCHESTER => 'CardWinchester',
		CARD_BANG => 'CardBang',
		CARD_MISSED => 'CardMissed',
		CARD_STAGECOACH => 'CardStagecoach',
		CARD_WELLS_FARGO => 'CardWellsFargo',
		CARD_BEER => 'CardBeer',
		CARD_GATLING => 'CardGatling',
		CARD_PANIC => 'CardPanic',
		CARD_CAT_BALOU => 'CardCatBalou',
		CARD_DUEL => 'CardDuel',
		CARD_SALOON => 'CardSaloon',
		CARD_GENERAL_STORE => 'CardGeneralStore',
		CARD_INDIANS => 'CardIndians',
		CARD_JAIL => 'CardJail',
		CARD_DYNAMITE => 'CardDynamite',
		CARD_BARREL => 'CardBarrel',
		CARD_SCOPE => 'CardScope',
		CARD_MUSTANG => 'CardMustang'

		/*CARD_PUNCH => 'CardPunch',
		CARD_SPRINGFIELD => 'CardSpringfield',
		CARD_CANNON => 'CardCannon',
		CARD_DODGE => 'CardDodge',
		CARD_WHISKY => 'CardWhisky',
		CARD_TEQUILA => 'CardTequila',
		CARD_BRAWL => 'CardBrawl',
		CARD_RAG_TIME => 'CardRagTime',*/
	];

	public function getUiData()
	{
		$ui = [];
		foreach ($this->getAll() as $card) {
			$ui[] = $card->getUiData();
		}
		return $ui;
	}


	/*
	 * getAll: return all type of cards
	 */
	public function getAll()
	{
		return array_map(function ($type){
			return $this->getCardByType($type);
		}, array_keys(self::$classes));
	}



	/*
	 * getCardOfType: factory function to create a card given its type
	 */
	public function getCardByType($cardType)
	{
		if (!isset(self::$classes[$cardType])) {
			throw new BgaVisibleSystemException("getCardByType: Unknown card $cardType");
		}
		return new self::$classes[$cardType]();
	}

}
