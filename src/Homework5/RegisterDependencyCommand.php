<?php

namespace App\Homework5;

use App\Homework3\ICommand;
use Closure;

class RegisterDependencyCommand implements ICommand
{
    private string $dependency;
    private Closure $dependencyResolverStrategy;

    public function __construct(string $dependency, Closure $dependencyResolverStrategy)
    {
        $this->dependency = $dependency;
        $this->dependencyResolverStrategy = $dependencyResolverStrategy;
    }

    public function execute(): void
    {
        /**
         * @var IoCScope $currentScope
         */
        $currentScope = Ioc::Resolve('IoC.Scope.Current');
        $currentScope->addStrategy($this->dependency, $this->dependencyResolverStrategy);
    }
}
