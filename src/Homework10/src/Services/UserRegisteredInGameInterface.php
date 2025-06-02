<?php

namespace App\Services;

use App\Entity\Game;
use App\Entity\User;

interface UserRegisteredInGameInterface
{
    public function checkAccess(User $user, Game $game): void;
}
