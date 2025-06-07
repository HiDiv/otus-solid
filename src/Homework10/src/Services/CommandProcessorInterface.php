<?php

namespace App\Services;

use App\Entity\Game;
use App\Entity\User;

interface CommandProcessorInterface
{
    public function process(User $user, Game $game, array $commandParams): void;
}
