<?php
namespace BANG\States;
use BANG\Managers\Players;
use BANG\Core\Log;
use BANG\Core\Stack;

trait DrawCardsTrait
{
  /*
   * stDrawCards: called after the beggining of each player turn, if the turn was not skipped or if no character's abilities apply
   */
  public function stDrawCards()
  {
    $player = Players::getActive();
    $player->drawCardsPhaseOne();
    Stack::finishState();
  }

  /************************
   **** drawCard state ****
   ***********************/
  // Only happens for specific character that can draw in hand of other player for instance
  public function argDrawCard()
  {
    $player = Players::getActive();
    return [
      '_private' => [
        'active' => $player->argDrawCard(),
      ],
    ];
  }

  public function draw($selected)
  {
    Players::getActive()->useAbility(['selected' => $selected]);
    Stack::finishState();
  }
}
