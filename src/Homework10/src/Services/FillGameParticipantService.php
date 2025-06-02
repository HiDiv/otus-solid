<?php

namespace App\Services;

use App\Entity\Game;

class FillGameParticipantService implements FillGameParticipantInterface
{
    public function __construct(
        private readonly UserFinderInterface $userFinder,
    ) {
    }

    public function fillParticipant(Game $game, array $participantLogins): void
    {
        foreach ($participantLogins as $login) {
            $user = $this->userFinder->findOneByLogin($login);
            if ($user) {
                $game->getParticipants()->add($user);
            }
        }
    }
}
