<?php

namespace App\Homework3;

class CommandRepeater implements ICommand
{
    private ICommand $repeatableCommand;

    public function __construct(ICommand $repeatableCommand)
    {
        $this->repeatableCommand = $repeatableCommand;
    }


    public function execute(): void
    {
        $this->repeatableCommand->execute();
    }
}
