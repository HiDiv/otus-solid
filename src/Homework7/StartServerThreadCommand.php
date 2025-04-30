<?php

namespace App\Homework7;

use App\Homework3\ExceptionHandler;
use App\Homework3\ICommand;
use parallel\Runtime;

class StartServerThreadCommand implements ICommand
{
    private Runtime $runtime;
    private string $channelName;

    public function __construct(Runtime $runtime, string $channelName)
    {
        $this->runtime = $runtime;
        $this->channelName = $channelName;
    }

    public function execute(): void
    {
        $this->runtime->run(
            function (string $channelName) {
                require_once __DIR__ . '/../../vendor/autoload.php';

                $receiver = new ReceiverQueue($channelName);
                $exceptionHandler = new ExceptionHandler();
                $serverThread = new ServerThread($receiver, $exceptionHandler);

                $exceptionHandler->register(
                    '',
                    '',
                    function () {
                        return new EmptyCommand();
                    }
                );
                $exceptionHandler->register(
                    HardStopExceptionCommand::class,
                    HardStopException::class,
                    function () use ($serverThread) {
                        return new HardStopServerCommand($serverThread);
                    }
                );
                $exceptionHandler->register(
                    SoftStopExceptionCommand::class,
                    SoftStopException::class,
                    function () use ($serverThread) {
                        return new SoftStopServerCommand($serverThread);
                    }
                );

                $serverThread->run();
            },
            [$this->channelName]
        );
    }
}
