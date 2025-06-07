<?php

namespace App\Controller;

use App\Entity\User;
use App\Services\DecodeParamsInterface;
use App\Services\GameAuthorizeInterface;
use App\Services\RequestErrorHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

final class AuthorizeController extends AbstractController
{
    public function __construct(
        private readonly RequestErrorHandlerInterface $errorHandler,
        private readonly DecodeParamsInterface $decodeParams,
        private readonly GameAuthorizeInterface $gameAuthorize
    ) {
    }

    #[Route('/authorize', methods: ['POST'])]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function authorize(Request $req): JsonResponse
    {
        try {
            /** @var User $user */
            $user = $this->getUser();
            $data = $this->decodeParams->decode($req->getContent());
            $token = $this->gameAuthorize->authorizeGame($user, $data['gameId'] ?? '');

            return $this->json(['token' => $token]);
        } catch (Throwable $exception) {
            return $this->errorHandler->handle($exception, $req);
        }
    }
}
