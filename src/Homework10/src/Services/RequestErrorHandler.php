<?php

namespace App\Services;

use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Service\ServiceProviderInterface;
use Throwable;

class RequestErrorHandler implements RequestErrorHandlerInterface
{
    public const DEFAULT_STRATEGY = 'default';

    public function __construct(
        #[AutowireLocator(ErrorHandlerStrategyInterface::class)]
        private readonly ServiceProviderInterface $handlers,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handle(Throwable $exception, Request $req): JsonResponse
    {
        $exceptionClass = get_class($exception);
        if ($this->handlers->has($exceptionClass)) {
            /** @var ErrorHandlerStrategyInterface $handler */
            $handler = $this->handlers->get($exceptionClass);
            return $handler($exception, $req);
        }

        if ($this->handlers->has(self::DEFAULT_STRATEGY)) {
            /** @var ErrorHandlerStrategyInterface $handler */
            $handler = $this->handlers->get(self::DEFAULT_STRATEGY);
            return $handler($exception, $req);
        }

        throw $exception;
    }
}
