<?php

namespace App\Homework3;

class CommandQuery
{
    /**
     * @var ICommand[]
     */
    private array $query = [];

    public function take(): ?ICommand
    {
        return array_shift($this->query);
    }

    public function push(ICommand $command): void
    {
        $this->query[] = $command;
    }
}
