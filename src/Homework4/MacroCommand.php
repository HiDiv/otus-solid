<?php

namespace App\Homework4;

use App\Homework3\ICommand;
use Throwable;

class MacroCommand implements ICommand
{
    /**
     * @var ICommand[]
     */
    private array $commands;

    public function __construct(array $commands)
    {
        $this->commands = $commands;
    }

    /**
     * @throws CommandException
     */
    public function execute(): void
    {
        try {
            foreach ($this->commands as $command) {
                $command->execute();
            }
        } catch (Throwable $exception) {
            throw new CommandException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}
