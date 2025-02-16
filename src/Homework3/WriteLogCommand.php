<?php

namespace App\Homework3;

class WriteLogCommand implements ICommand
{
    private ILog $log;
    private string $message;

    public function __construct(ILog $log, string $message)
    {
        $this->log = $log;
        $this->message = $message;
    }

    public function execute(): void
    {
        $this->log->log($this->message);
    }
}
