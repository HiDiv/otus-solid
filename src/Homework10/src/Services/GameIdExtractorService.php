<?php

namespace App\Services;

use App\Exceptions\GameNotFound;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use LogicException;
use Symfony\Component\HttpFoundation\Request;

class GameIdExtractorService implements GameIdExtractorInterface
{
    public function __construct(
        private readonly JWTTokenManagerInterface $jwtManager,
    ) {
    }

    /**
     * @throws GameNotFound
     */
    public function extract(Request $req): string
    {
        $authHeader = $req->headers->get('Authorization', '');
        if (!str_starts_with($authHeader, 'Bearer ')) {
            throw new LogicException('Missing or malformed Authorization header');
        }
        $jwtString = substr($authHeader, 7);

        $payload = $this->jwtManager->parse($jwtString);
        $gameId = $payload['game'] ?? '';
        if (empty($gameId)) {
            throw new GameNotFound('JWT payload does not contain game ID');
        }

        return $gameId;
    }
}
