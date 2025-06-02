<?php

namespace App\Services;

use App\Entity\Game;

interface CreateGameInterface
{
    public function createGame(array $participantLogins): Game;
}
