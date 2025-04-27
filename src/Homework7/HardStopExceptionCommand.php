<?php

namespace App\Homework7;

use App\Homework3\ICommand;

class HardStopExceptionCommand implements ICommand
{
    public function execute(): void
    {
        throw new HardStopException();
    }
}
