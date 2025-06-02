<?php

namespace App\Strategies;

use App\Services\ErrorHandlerStrategyInterface;
use App\Services\RequestErrorHandler;
use Symfony\Component\DependencyInjection\Attribute\AsTaggedItem;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

#[AsTaggedItem(RequestErrorHandler::DEFAULT_STRATEGY)]
class DefaultStrategy implements ErrorHandlerStrategyInterface
{
    public function __invoke(Throwable $exception, Request $req): JsonResponse
    {
        return new JsonResponse(['error' => $exception->getMessage()], 500);
    }
}
