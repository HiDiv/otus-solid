<?php

namespace App\Homework7;

use App\Homework3\ICommand;

class HardStopServerCommand implements ICommand
{
    private ServerThreadInterface $serverThread;

    public function __construct(ServerThreadInterface $serverThread)
    {
        $this->serverThread = $serverThread;
    }

    public function execute(): void
    {
        $this->serverThread->hardStop();
    }
}
