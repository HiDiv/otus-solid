<?php

namespace App\Homework7;

use App\Homework3\ICommand;

class EmptyCommand implements ICommand
{
    private string $mark = 'empty';

    public function execute(): void
    {
    }
}
