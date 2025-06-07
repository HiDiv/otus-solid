<?php

namespace App\Services;

use App\Entity\User;
use Symfony\Component\Uid\Uuid;

interface CreateTokenForGameInterface
{
    public function createToken(User $user, Uuid $gameId): string;
}
