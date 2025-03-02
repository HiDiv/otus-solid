<?php

namespace App\Homework5;

use App\Homework3\ICommand;
use Closure;

class SetCurrentScopeCommand implements ICommand
{
    private Closure $setCurrentScopes;
    private IoCScope $newScopes;

    public function __construct(Closure $setCurrentScopes, IoCScope $newScopes)
    {
        $this->setCurrentScopes = $setCurrentScopes;
        $this->newScopes = $newScopes;
    }

    public function execute(): void
    {
        ($this->setCurrentScopes)($this->newScopes);
    }
}
