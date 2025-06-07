<?php

namespace App\Services;

use App\Entity\User;

interface GameAuthorizeInterface
{
    public function authorizeGame(User $user, string $gameIdStr): string;
}
