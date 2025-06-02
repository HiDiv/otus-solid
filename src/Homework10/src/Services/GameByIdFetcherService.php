<?php

namespace App\Services;

use App\Entity\Game;
use App\Exceptions\ErrorDecodeParams;
use App\Exceptions\GameNotFound;
use InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

class GameByIdFetcherService implements GameByIdFetcherInterface
{
    public function __construct(
        private readonly GameFinderInterface $gameFinder,
    ) {
    }

    /**
     * @throws ErrorDecodeParams
     * @throws GameNotFound
     */
    public function fetch(string $gameIdStr): Game
    {
        $gameId = $this->parseUuid($gameIdStr);

        $game = $this->gameFinder->findById($gameId);
        if (!$game) {
            throw new GameNotFound('Game not found');
        }

        return $game;
    }

    /**
     * @throws ErrorDecodeParams
     */
    private function parseUuid(string $idStr): Uuid
    {
        if (empty($idStr)) {
            throw new ErrorDecodeParams('gameId is required');
        }

        try {
            return Uuid::fromString($idStr);
        } catch (InvalidArgumentException $ex) {
            throw new ErrorDecodeParams('gameId is not a valid UUID', 0, $ex);
        }
    }
}
