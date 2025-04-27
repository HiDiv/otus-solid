<?php

namespace App\Homework7;

use App\Homework3\ICommand;

interface ReceiverInterface
{
    public function receive(): ICommand;
    public function empty(): bool;
}
