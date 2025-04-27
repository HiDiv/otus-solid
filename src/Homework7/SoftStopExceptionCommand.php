<?php

namespace App\Homework7;

use App\Homework3\ICommand;

class SoftStopExceptionCommand implements ICommand
{
    public function execute(): void
    {
        throw new SoftStopException();
    }
}
