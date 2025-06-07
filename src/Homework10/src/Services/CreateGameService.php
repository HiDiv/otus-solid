<?php

namespace App\Services;

use App\Entity\Game;

class CreateGameService implements CreateGameInterface
{
    public function __construct(
        private readonly FillGameParticipantInterface $fillGameParticipant,
        private readonly GameNotEmptyValidatorInterface $gameValidator,
        private readonly GamePersisterInterface $gamePersister,
    ) {
    }

    public function createGame(array $participantLogins): Game
    {
        $game = new Game();
        $this->fillGameParticipant->fillParticipant($game, $participantLogins);
        $this->gameValidator->gameNotEmpty($game);
        $this->gamePersister->save($game);

        return $game;
    }
}
