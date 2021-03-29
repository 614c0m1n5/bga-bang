<?php
namespace BANG\Models;
use BANG\Managers\Players;
use BANG\Core\Notifications;

/*
 * AbstractCard: base class to handle actions cards
 */
class AbstractCard implements \JsonSerializable
{
	public function __construct($row = null)
	{
    if($row != null){
      $this->id = $row['id'];
      $this->value = $row['value'];
      $this->color = $row['color'];
    }
	}

	/*
	 * Attributes
	 */
	protected $id;
  protected $color;
  protected $value;

  // Static informations about cards
  protected $type;
	protected $name;
	protected $text;
	protected $symbols;
	protected $effect; // array with type, impact and sometimes range
	protected $copies = [];

	/*
	 * getUiData: used in frontend to display cards
	 */
	public function getUIData() {
		return [
			'type' => $this->type,
			'name' => $this->name,
			'text' => $this->text
		];
	}

	/*
	 * jsonSerialize: used in frontend to manipulate cards
	 */
	public function jsonSerialize() {
		return [
			'id' => $this->id,
			'type' => $this->type,
			'color' => $this->color,
			'value' => $this->value,
		];
	}


	/*
	 * Getters
	 */
	public function getId()			{ return $this->id; }
	public function getType()		{ return $this->type; }
	public function getName()		{ return $this->name; }
	public function getText()		{ return $this->text; }
	public function getEffect()	{ return $this->effect; }
	public function getSymbols(){ return $this->symbols; }
	public function getCopies()	{ return $this->copies; }
	public function getCopy()		{ return $this->copy; }

	public function getColor()	{ return null; } // Will be overwrite by Blue/Brown class
	public function getCopyValue() { return $this->value; }
	public function getCopyColor() { return $this->color; }
	public function getEffectType(){ return $this->effect['type']; }

	public function isEquipment(){ return false; }
	public function isAction()	 { return false; }
  public function isWeapon()	 { return $this->effect['type'] == WEAPON; }
	public function getNameAndValue() {
		$colors = ['H' => clienttranslate('Hearts'), 'C' => clienttranslate('Clubs'), 'D' => clienttranslate('Diamonds'), 'S' => clienttranslate('Spades')];
		$format = $this->format();
		return $this->name . ' (' . $colors[$format['color']] . ' ' . $format['value'] . ')';
	}


	public function wasPlayed()	{ return Cards::wasPlayed($this->id);	}
	public function discard() { Cards::discardCard($this); }


	/**
	 * getPlayOption : default function to know which card can be played by $player
	 * return: type of option and targets if any
	 */
  public function getPlayOptions($player) {
		return [];
	}

	/**
	 * play : default function to play a card that. Can be used for cards that have only symbols
	 * return: null if the game should continue the play loop, "stateName" if another state need to be called
	 */
	public function play($player, $args) { }


	/**
	 * getReactionOptions: default function to handle possible reaction (attack => defense)
	 * return: list of options (cards/abilities) that can be used
	 */
	public function getReactionOptions($player) {
		$options = $player->getDefensiveOptions();
		return $options;
	}

	/**
	 * react: default function to handle reaction using a card
	 */
	public function react($card, $player) {
		if($this->effect['type'] == BASIC_ATTACK){
			if($card->getColor() == BROWN) {
				Notifications::cardPlayed($player, $card);
				return Cards::playCard($card->id);
			} else {
				// TODO : notification to highlight the card
				return $card->activate($player);
			}
		}
		return null;
	}

	/**
	 * pass: default function to handle reaction by clicking "pass" button
	 */
	public function pass($player) {
		if($this->effect['type'] == BASIC_ATTACK)
			return $player->looseLife();
		return null;
	}


	/**
	 * function to overwrite by blue cards like barrel, jail, dynamite
	 */
	public function activate($player, $args = []) { return null; }


  /**
	 * can be overwritten to add an additional Message to the played card notification.
	 * this message should start with a space
	 */
	public function getArgsMessage($args) {
		if(isset($args['player']) && !is_null($args['player']) ) {
      return [
        'name' => Players::getPlayer($args['player'])->getName(),
      ];
		}
		return null;
	}
}