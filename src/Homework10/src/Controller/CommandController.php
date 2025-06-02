<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\CommandProcessorInterface;
use App\Services\DecodeParamsInterface;
use App\Services\GameByIdFetcherInterface;
use App\Services\GameIdExtractorInterface;
use App\Services\RequestErrorHandlerInterface;
use App\Services\UserRegisteredInGameInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

final class CommandController extends AbstractController
{
    public function __construct(
        private readonly RequestErrorHandlerInterface $errorHandler,
        private readonly GameIdExtractorInterface $gameIdExtractor,
        private readonly DecodeParamsInterface $decodeParams,
        private readonly GameByIdFetcherInterface $gameByIdFetcher,
        private readonly UserRegisteredInGameInterface $userRegisteredInGame,
        private readonly CommandProcessorInterface $commandProcessor,
    ) {
    }

    #[Route('/command', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function command(Request $req): JsonResponse
    {
        try {
            $gameId = $this->gameIdExtractor->extract($req);
            $data = $this->decodeParams->decode($req->getContent());

            /** @var User $user */
            $user = $this->getUser();
            $game = $this->gameByIdFetcher->fetch($gameId);
            $this->userRegisteredInGame->checkAccess($user, $game);

            $this->commandProcessor->process($user, $game, $data['command'] ?? []);

            return $this->json(['status' => 'Command accepted']);
        } catch (Throwable $exception) {
            return $this->errorHandler->handle($exception, $req);
        }
    }
}
