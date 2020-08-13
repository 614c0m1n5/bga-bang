<?php

/*
 * BangNotificationmanager: manages notifications
 */
class BangNotificationManager extends APP_GameClass {

  public static function cardPlayed($player, $card, $args = []) {

    bang::$instance->notifyAllPlayers('cardPlayed', clienttranslate('${player_name} plays ${card_name}${card_msg}'), [
      'i18n' => ['card_name', 'card_msg'],
      'player_name' => $player->getName(),
      'card_name' => $card->getName(),
      'card_msg' => $card->getArgsMessage($args),
      'card' => $card->format(),
      'playerId' => $player->getId(),
      'targetPlayer' => isset($args['player']) ? $args['player'] : null,
      'target' => $card->isEquipment() ? 'inPlay' : 'discard'
    ]);
  }

  public static function lostLife($player, $amount = 1) {
    $msg  = $amount == 1 ? clienttranslate('${player_name} looses a life point') : clienttranslate('${player_name} looses ${amount} life points');
    bang::$instance->notifyAllPlayers('updateHP', $msg, [
      'player_name' => $player->getName(),
      'playerId' => $player->getId(),
      'hp' => $player->getHp(),
      'amount' => -$amount
    ]);

  }

  public static function gainedLife($player, $amount) {
    $msg  = $amount == 1 ? clienttranslate('${player_name} gains a life point') : clienttranslate('${player_name} gains ${amount} life points');
    bang::$instance->notifyAllPlayers('updateHP', $msg, [
      'player_name' => $player->getName(),
      'amount' => $amount,
      'playerId' => $player->getId(),
      'hp'=>$player->getHp()
    ]);
  }

  public static function gainedCard($player, $cards) {
    $amount = count($cards);
    $msg  = $amount == 1 ? clienttranslate('${player_name} draws a card') : clienttranslate('${player_name} draws ${amount} cards');
    $formattedCards = array_map(function($card){ return $card->getUIData(); }, $cards);
    bang::$instance->notifyPlayer($player->getId(), "cardsGained", '', [
      'cards' => $formattedCards,
      'src' => 'deck'
    ]);
    bang::$instance->notifyAllPlayers("updateHand", $msg, [
      'player_name' => $player->getName(),
      'playerId' => $player->getId(),
      'amount' => $amount,
    ]);
  }

  public static function discardedCard($player, $card, $silent = false) {
    bang::$instance->notifyAllPlayers("cardLost", '', [
      'playerId' => $player->getId(),
      'card' => $card->format(),
    ]);
    if($silent)
      return;

    bang::$instance->notifyAllPlayers("updateHand", clienttranslate('${player_name} discard ${card_name}'), [
      'i18n' => ['card_name'],
      'player_name' => $player->getName(),
      'card_name' => $card->getName(),
      'playerId' => $player->getId(),
      'amount' => -1
    ]);
  }

  public static function discardedCards($player, $cards, $silent = false) {
    foreach($cards as $card){
      self::discardedCard($player, $card, $silent);
    }
  }


  public static function stoleCard($receiver, $victim, $card, $equipped) {
    bang::$instance->notifyPlayer($player->getId(), "cardsGained", '', [
      'playerId' => $player->getId(),
      'cards' => [$card->format()],
      'src' => $victim->getId()
    ]);
    bang::$instance->notifyPlayer($victim->getId(), "cardsLost", '', [
      'playerId' => $player->getId(),
      'cards' => [$card->format()],
      'src' => $victim->getId()
    ]);


    $msg = $equipped? clienttranslate('${player_name} stole ${card_name} from ${victim_name}')
                    : clienttranslate('${player_name} stole a card from ${victim_name}');
    $data = [
      'playerId'=> $receveir->getId(),
      'player_name' => $receiver->getName(),
      'victim_name' => $victim->getName(),
    ];
    if($equipped){
      $data['i18n'] = ['card_name'];
      $data['card_name'] = $card->getName();
    }

    // TODO : weird stuff with count()
    bang::$instance->notifyAllPlayers("updateHand", $msg, ['playerId'=>$player->getId(), 'amount'=>count($cards)]);
    bang::$instance->notifyAllPlayers("updateHand", '', ['playerId'=>$victim->getId(), 'amount'=>-count($cards)]);
  }

}
