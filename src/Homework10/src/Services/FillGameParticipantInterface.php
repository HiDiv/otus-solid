<?php

namespace App\Services;

use App\Entity\Game;

interface FillGameParticipantInterface
{
    public function fillParticipant(Game $game, array $participantLogins): void;
}
