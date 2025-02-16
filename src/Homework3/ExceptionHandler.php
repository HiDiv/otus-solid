<?php

namespace App\Homework3;

use Throwable;

class ExceptionHandler
{
    private array $handlers = [];

    /**
     * @throws Throwable
     */
    public function handle(ICommand $command, Throwable $exception): ICommand
    {
        $commandClass = get_class($command);
        $exceptionClass = get_class($exception);

        if ($this->hasHandler($commandClass, $exceptionClass)) {
            return $this->callHandler($commandClass, $exceptionClass, $command, $exception);
        }

        if ($this->hasHandler($commandClass, '')) {
            return $this->callHandler($commandClass, '', $command, $exception);
        }

        if ($this->hasHandler('', $exceptionClass)) {
            return $this->callHandler('', $exceptionClass, $command, $exception);
        }

        if ($this->hasHandler('', '')) {
            return $this->callHandler('', '', $command, $exception);
        }

        throw $exception;
    }

    private function hasHandler(string $commandClass, string $exceptionClass): bool
    {
        return isset($this->handlers[$commandClass][$exceptionClass]);
    }

    private function callHandler(string $commandClass, string $exceptionClass, ICommand $command, Throwable $exception): ICommand
    {
        $handler = $this->handlers[$commandClass][$exceptionClass];
        return $handler($command, $exception);
    }

    public function register(string $commandClass, string $exceptionClass, callable $handler): void
    {
        $this->handlers[$commandClass][$exceptionClass] = $handler;
    }
}
