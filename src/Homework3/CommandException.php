<?php

namespace App\Homework3;

use Throwable;

class CommandException implements ICommand
{
    private Throwable $exception;

    /**
     * @param Throwable $exception
     */
    public function __construct(Throwable $exception)
    {
        $this->exception = $exception;
    }

    /**
     * @throws Throwable
     */
    public function execute(): void
    {
        throw $this->exception;
    }
}
