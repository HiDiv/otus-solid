<?php

namespace App\Homework5;

use App\Homework3\ICommand;
use Closure;
use RuntimeException;
use ValueError;

class InitScopeCommand implements ICommand
{
    /**
     * @var IoCScope|null
     */
    protected static ?IoCScope $currentScopes = null;

    /**
     * @var IoCScope
     */
    protected static IoCScope $rootScope;

    protected bool $alreadyExecutes = false;

    public function execute(): void
    {
        if ($this->alreadyExecutes) {
            return;
        }

        self::$currentScopes = null;
        self::$rootScope = new IoCScope(0);
        self::$rootScope->addStrategy('IoC.Scope.Current.Set', $this->setCurrentScopeStrategy());
        self::$rootScope->addStrategy('IoC.Scope.Current.Clear', $this->clearCurrentScopeStrategy());
        self::$rootScope->addStrategy('IoC.Scope.Current', $this->currentScopeStrategy());
        self::$rootScope->addStrategy('IoC.Scope.Create.Empty', $this->emptyCreateScopeStrategy());
        self::$rootScope->addStrategy('IoC.Scope.Create', $this->createScopeStrategy());
        self::$rootScope->addStrategy('IoC.Register', $this->registerStrategy());

        $registerCommand = $this->registerCommand();
        $registerCommand->execute();

        $this->alreadyExecutes = true;
    }

    protected function setCurrentScopeStrategy(): Closure
    {
        return function (...$params) {
            if (!isset($params[0]) || !($params[0] instanceof IoCScope)) {
                throw new ValueError('Первый параметр должен быть экземпляром IoCScope');
            }

            return new SetCurrentScopeCommand($this->setParentScopeFunc(), $params[0]);
        };
    }

    protected function setParentScopeFunc(): Closure
    {
        return static function (?IoCScope $newScopes): void {
            InitScopeCommand::$currentScopes = $newScopes;
        };
    }

    protected function clearCurrentScopeStrategy(): Closure
    {
        return function () {
            return new ClearCurrentScopeCommand($this->setParentScopeFunc());
        };
    }

    protected function currentScopeStrategy(): Closure
    {
        return static function () {
            return InitScopeCommand::$currentScopes ?? InitScopeCommand::$rootScope;
        };
    }

    protected function emptyCreateScopeStrategy(): Closure
    {
        return static function () {
            return new IoCScope();
        };
    }

    protected function createScopeStrategy(): Closure
    {
        return static function (...$params) {
            $parentScope = $params[0] ?? IoC::resolve('IoC.Scope.Current');
            $creatingScope = IoC::resolve('IoC.Scope.Create.Empty');
            $creatingScope->addStrategy('IoC.Scope.Parent', function () use ($parentScope) {
                return $parentScope;
            });

            return $creatingScope;
        };
    }

    protected function registerStrategy(): Closure
    {
        return static function (...$params) {
            return new RegisterDependencyCommand($params[0], $params[1]);
        };
    }

    protected function registerCommand(): ICommand
    {
        return IoC::resolve(
            'Update Ioc Resolve Dependency Strategy',
            static function () {
                return static function (string $dependency, ...$params) {
                    $scope = InitScopeCommand::$currentScopes ?? InitScopeCommand::$rootScope;
                    return (new DependencyResolver($scope))->resolve($dependency, ...$params);
                };
            }
        );
    }
}
