<?php

namespace App\Homework5;

use ValueError;

class DependencyResolver
{
    private IoCScope $scopes;

    public function __construct(IoCScope $scopes)
    {
        $this->scopes = $scopes;
    }

    public function resolve(string $dependency, ...$params): mixed
    {
        $curScopes = $this->scopes;

        while (true) {
            if ($curScopes->hasStrategy($dependency)) {
                $dependencyResolverStrategy = $curScopes->getStrategy($dependency);
                return $dependencyResolverStrategy(...$params);
            }

            if (!$curScopes->hasStrategy('IoC.Scope.Parent')) {
                throw new ValueError(sprintf('Неизвестная зависимость IoC: %s', $dependency));
            }
            $getParentStrategy = $curScopes->getStrategy('IoC.Scope.Parent');
            $curScopes = $getParentStrategy(...$params);
        }

    }
}
