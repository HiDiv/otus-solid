<?php

namespace App\Services;

use App\Entity\Game;
use App\Entity\User;
use App\Exceptions\UserAccessInGameDenied;

class UserRegisteredInGameService implements UserRegisteredInGameInterface
{
    /**
     * @throws UserAccessInGameDenied
     */
    public function checkAccess(User $user, Game $game): void
    {
        if (!$game->getParticipants()->contains($user)) {
            throw new UserAccessInGameDenied('The user is not registered in the game');
        }
    }
}
