<?php

namespace App\Services;

use App\Entity\Game;

interface GameNotEmptyValidatorInterface
{
    public function gameNotEmpty(Game $game): void;
}
