<?php

namespace App\Homework3;

use Throwable;

class QueueHandler
{
    private CommandQuery $queue;
    private ExceptionHandler $exceptionHandler;

    public function __construct(CommandQuery $queue, ExceptionHandler $exceptionHandler)
    {
        $this->queue = $queue;
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * @throws Throwable
     */
    public function handle(): void
    {
        while ($command = $this->queue->take()) {
            try {
                $command->execute();
            } catch (Throwable $exception) {
                $newCommand = $this->exceptionHandler->handle($command, $exception);
                $newCommand->execute();
            }
        }
    }
}
