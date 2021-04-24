<?php
namespace BANG\States;
use BANG\Core\Stack;
use BANG\Managers\Cards;
use BANG\Managers\Players;

trait ResolveFlippedTrait
{
  /*
   * stFlipCard: add a node to flip card, to ensure multi-flip don't overlap
   */
  public function stFlipCard()
  {
    $atom = Stack::top();
    $player = Players::get($atom['pId']);
    $src = Cards::get($atom['src']['id']);
    Stack::shift();
    $player->flip($src);
    Stack::resolve();
  }

  /*
   * stResolveFlipped: called if Dynamite/Jail/Barrel required resolving
   */
  // Unlike stFlipCard() here we rely on reactAux() method to do Stack::nextState() for us
  // This is a terrible implementation as we need to control each atom inside this atom only
  // TODO: We need to refactor Stack to be more smart and control its own changes
  public function stResolveFlipped()
  {
    $atom = Stack::top();
    $player = Players::get($atom['pId']);
    $srcCard = $atom['src']['id'] == $atom['pId'] ? $player : Cards::get($atom['src']['id']);
    $flippedCards = Cards::getInLocation(LOCATION_FLIPPED);
    if ($flippedCards->count() == 1) {
      $srcCard->resolveFlipped($flippedCards->first(), $player, $atom['switchToNextState'] ?? null);
    } else {
      // Shouldn't ever happen. There should be just 1 card flipped
      throw new \BgaVisibleSystemException("There's {$flippedCards->count()} card in LOCATION_FLIPPED");
    }
    Cards::moveAllInLocation(LOCATION_FLIPPED, LOCATION_DISCARD);
    if (isset($atom['switchToNextState']) && $atom['switchToNextState']) {
      Stack::nextState();
    }
  }
}
