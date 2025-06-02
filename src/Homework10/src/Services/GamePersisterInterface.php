<?php

namespace App\Services;

use App\Entity\Game;

interface GamePersisterInterface
{
    public function save(Game $game): void;
}
