<?php

namespace App\Controller;

use App\Services\CreateGameInterface;
use App\Services\DecodeParamsInterface;
use App\Services\RequestErrorHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

final class AuthController extends AbstractController
{
    public function __construct(
        private readonly RequestErrorHandlerInterface $errorHandler,
        private readonly DecodeParamsInterface $decodeParams,
        private readonly CreateGameInterface $createGame,
    ) {
    }

    #[Route('/create-game', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function createGame(Request $req): JsonResponse
    {
        try {
            $data = $this->decodeParams->decode($req->getContent());
            $game = $this->createGame->createGame($data['participants'] ?? []);

            return $this->json(['gameId' => $game->getId()]);
        } catch (Throwable $exception) {
            return $this->errorHandler->handle($exception, $req);
        }
    }
}
