<?php

namespace App\Services;

use App\Entity\User;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Uid\Uuid;

class CreateTokenForGameService implements CreateTokenForGameInterface
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    public function createToken(User $user, Uuid $gameId): string
    {
        $payload = [
            'username' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
            'game' => $gameId,
        ];

        return $this->jwtManager->createFromPayload($user, $payload);
    }
}
