<?php

namespace App\Services;

use App\Entity\User;

class GameAuthorizeService implements GameAuthorizeInterface
{
    public function __construct(
        private readonly GameByIdFetcherInterface $gameByIdFetcher,
        private readonly UserRegisteredInGameInterface $userRegisteredInGame,
        private readonly CreateTokenForGameInterface $createTokenForGame
    ) {
    }

    public function authorizeGame(User $user, string $gameIdStr): string
    {
        $game = $this->gameByIdFetcher->fetch($gameIdStr);
        $this->userRegisteredInGame->checkAccess($user, $game);

        return $this->createTokenForGame->createToken($user, $game->getId());
    }
}
