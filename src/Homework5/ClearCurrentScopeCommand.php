<?php

namespace App\Homework5;

use App\Homework3\ICommand;
use Closure;

class ClearCurrentScopeCommand implements ICommand
{
    private Closure $setCurrentScopes;

    public function __construct(Closure $setCurrentScopes)
    {
        $this->setCurrentScopes = $setCurrentScopes;
    }

    public function execute(): void
    {
        ($this->setCurrentScopes)(null);
    }
}
