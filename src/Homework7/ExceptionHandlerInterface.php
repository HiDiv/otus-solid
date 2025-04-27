<?php

namespace App\Homework7;

use App\Homework3\ICommand;
use Throwable;

interface ExceptionHandlerInterface
{
    public function handle(ICommand $command, Throwable $exception): ICommand;
}
