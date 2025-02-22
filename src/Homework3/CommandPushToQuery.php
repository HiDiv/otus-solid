<?php

namespace App\Homework3;

class CommandPushToQuery implements ICommand
{
    private ICommand $command;
    private CommandQuery $query;

    public function __construct(ICommand $command, CommandQuery $query)
    {
        $this->command = $command;
        $this->query = $query;
    }


    public function execute(): void
    {
        $this->query->push($this->command);
    }
}
