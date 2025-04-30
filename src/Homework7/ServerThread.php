<?php

namespace App\Homework7;

use Closure;
use Throwable;

class ServerThread implements ServerThreadInterface
{
    private bool $stop = false;
    private ReceiverInterface $receiver;
    private ExceptionHandlerInterface $exceptionHandler;
    private Closure $behaviour;

    public function __construct(ReceiverInterface $receiver, ExceptionHandlerInterface $exceptionHandler)
    {
        $this->receiver = $receiver;
        $this->exceptionHandler = $exceptionHandler;
        $this->behaviour = $this->getDefaultBehaviour();
    }

    private function getDefaultBehaviour(): Closure
    {
        return function () {
            $command = $this->receiver->receive();
            try {
                $command->execute();
            } catch (Throwable $exception) {
                $this->exceptionHandler->handle($command, $exception)->execute();
            }
        };
    }

    public function run(): void
    {
        while (!$this->stop) {
            ($this->behaviour)();
        }
    }

    public function hardStop(): void
    {
        $this->stop = true;
    }

    public function softStop(): void
    {
        $oldBehaviour = $this->behaviour;
        $this->behaviour = function () use ($oldBehaviour) {
            if ($this->receiver->empty()) {
                $this->stop = true;
                return;
            }

            $oldBehaviour();
        };
    }
}
