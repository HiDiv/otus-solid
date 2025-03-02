<?php

namespace App\Homework5;

use Closure;
use ValueError;

class IoCScope
{
    /**
     * @var Closure[]
     */
    private array $strategy;

    public function __construct()
    {
        $this->strategy = [];
    }

    public function addStrategy(string $dependency, Closure $strategy): void
    {
        $this->strategy[$dependency] = $strategy;
    }

    public function getStrategy(string $dependency): Closure
    {
        if (!$this->hasStrategy($dependency)) {
            throw new ValueError(sprintf('Неизвестная зависимость IoC: %s', $dependency));
        }

        return $this->strategy[$dependency];
    }

    public function hasStrategy(string $dependency): bool
    {
        return isset($this->strategy[$dependency]);
    }
}
