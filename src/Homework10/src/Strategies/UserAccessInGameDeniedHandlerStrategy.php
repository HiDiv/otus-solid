<?php

namespace App\Strategies;

use App\Exceptions\UserAccessInGameDenied;
use App\Services\ErrorHandlerStrategyInterface;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

#[AsTaggedItem(UserAccessInGameDenied::class)]
class UserAccessInGameDeniedHandlerStrategy implements ErrorHandlerStrategyInterface
{
    public function __invoke(Throwable $exception, Request $req): JsonResponse
    {
        return new JsonResponse(['error' => $exception->getMessage()], 403);
    }
}
