<?php

namespace App\Services;

use App\Entity\Game;
use App\Exceptions\EmptyGame;

class GameNotEmptyValidatorService implements GameNotEmptyValidatorInterface
{
    /**
     * @throws EmptyGame
     */
    public function gameNotEmpty(Game $game): void
    {
        if ($game->getParticipants()->isEmpty()) {
            throw new EmptyGame('No participants found while creating the game');
        }
    }
}
