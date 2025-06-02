<?php

namespace App\Services;

use App\Entity\Game;

interface GameByIdFetcherInterface
{
    public function fetch(string $gameIdStr): Game;
}
