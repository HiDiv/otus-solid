<?php

namespace App\Homework5;

use App\Homework3\ICommand;
use Closure;
use ValueError;

class IoC
{
    protected static ?Closure $strategy = null;

    public static function resolve(string $dependency, ...$params): mixed
    {
        if ($dependency === 'Update Ioc Resolve Dependency Strategy') {
            if (!isset($params[0]) || !($params[0] instanceof Closure)) {
                throw new ValueError('Первый параметр должен быть экземпляром Closure');
            }

            return self::getBootloaderStrategyCommand($params[0], self::getStrategyFunc(), self::setStrategyFunc());
        }

        if (empty(self::$strategy)) {
            throw new ValueError(sprintf('Неизвестная зависимость IoC: %s', $dependency));
        }

        return (self::$strategy)($dependency, ...$params);
    }

    protected static function getBootloaderStrategyCommand(
        Closure $newStrategyUpdater,
        Closure $getStrategy,
        Closure $setStrategy
    ): ICommand {
        return new class($newStrategyUpdater, $getStrategy, $setStrategy) implements ICommand {
            private Closure $updater;
            private Closure $getStrategy;
            private Closure $setStrategy;

            public function __construct(Closure $updater, Closure $getStrategy, Closure $setStrategy)
            {
                $this->updater = $updater;
                $this->getStrategy = $getStrategy;
                $this->setStrategy = $setStrategy;
            }

            public function execute(): void
            {
                ($this->setStrategy)(($this->updater)(($this->getStrategy)()));
            }
        };
    }

    protected static function getStrategyFunc(): Closure
    {
        return static function (): ?Closure {
            return IoC::$strategy;
        };
    }

    protected static function setStrategyFunc(): Closure
    {
        return static function (?Closure $strategy): void {
            IoC::$strategy = $strategy;
        };
    }
}
