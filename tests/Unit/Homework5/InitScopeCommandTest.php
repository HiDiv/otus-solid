<?php

namespace Tests\Unit\Homework5;

use App\Homework3\ICommand;
use App\Homework5\InitScopeCommand;
use App\Homework5\IoC;
use App\Homework5\IoCScope;
use App\Homework5\SetCurrentScopeCommand;
use Closure;
use Codeception\Test\Unit;
use RuntimeException;
use Tests\Support\UnitTester;
use ValueError;

class InitScopeCommandTest extends Unit
{
    protected UnitTester $tester;
    protected InitScopeCommand $initScopeCommand;

    public function testNotSetYetScope(): void
    {
        $rootScope = IoC::resolve('IoC.Scope.Current');

        $this->tester->assertInstanceOf(IoCScope::class, $rootScope);
    }

    public function testCreateNewScope(): void
    {
        $newScope = IoC::resolve('IoC.Scope.Create');

        $this->tester->assertInstanceOf(IoCScope::class, $newScope);
        $this->tester->assertNotSame(Closure::class, $newScope->getStrategy('IoC.Scope.Parent'));
    }

    public function testSetNewScope(): void
    {
        $rootScope = IoC::resolve('IoC.Scope.Current');
        $newScope = IoC::resolve('IoC.Scope.Create');

        $setCurrentScopeCommand = IoC::resolve('IoC.Scope.Current.Set', $newScope);

        $this->tester->assertInstanceOf(ICommand::class, $setCurrentScopeCommand);

        $setCurrentScopeCommand->execute();
        $currentScope = IoC::resolve('IoC.Scope.Current');

        $this->tester->assertInstanceOf(IoCScope::class, $currentScope);
        $this->tester->assertSame($newScope, $currentScope);
        $this->tester->assertNotSame($rootScope, $currentScope);
    }

    public function testGetParentFromScope(): void
    {
        $rootScope = IoC::resolve('IoC.Scope.Current');
        $newScope = IoC::resolve('IoC.Scope.Create');
        /** @var ICommand $setNewScopeCommand */
        $setNewScopeCommand = IoC::resolve('IoC.Scope.Current.Set', $newScope);
        $setNewScopeCommand->execute();

        $expectRootScope1 = IoC::resolve('IoC.Scope.Parent');

        $this->tester->assertInstanceOf(IoCScope::class, $expectRootScope1);
        $this->tester->assertSame($rootScope, $expectRootScope1);

        $nextScope = IoC::resolve('IoC.Scope.Create');
        /** @var ICommand $setNextScopeCommand */
        $setNextScopeCommand = IoC::resolve('IoC.Scope.Current.Set', $nextScope);
        $setNextScopeCommand->execute();

        $expectNewScope = IoC::resolve('IoC.Scope.Parent');

        $this->tester->assertInstanceOf(IoCScope::class, $expectNewScope);
        $this->tester->assertSame($newScope, $expectNewScope);
        $this->tester->assertNotSame($rootScope, $expectNewScope);

        $siblingScope = IoC::resolve('IoC.Scope.Create', $rootScope);
        /** @var ICommand $setNextScopeCommand */
        $setSiblingScopeCommand = IoC::resolve('IoC.Scope.Current.Set', $siblingScope);
        $setSiblingScopeCommand->execute();

        $expectRootScope2 = IoC::resolve('IoC.Scope.Parent');

        $this->tester->assertInstanceOf(IoCScope::class, $expectRootScope2);
        $this->tester->assertNotSame($newScope, $expectRootScope2);
        $this->tester->assertSame($rootScope, $expectRootScope2);
    }

    public function testGetParentWithoutScope(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Неизвестная зависимость IoC: IoC.Scope.Parent');

        IoC::resolve('IoC.Scope.Parent');
    }

    public function testClearExistingScope(): void
    {
        $rootScope = IoC::resolve('IoC.Scope.Current');
        $newScope = IoC::resolve('IoC.Scope.Create');
        /** @var ICommand $setNewScopeCommand */
        $setNewScopeCommand = IoC::resolve('IoC.Scope.Current.Set', $newScope);
        $setNewScopeCommand->execute();

        $expectNewScope = IoC::resolve('IoC.Scope.Current');

        $this->tester->assertInstanceOf(IoCScope::class, $expectNewScope);
        $this->tester->assertSame($newScope, $expectNewScope);
        $this->tester->assertNotSame($rootScope, $expectNewScope);

        $clearCurrentScopeCommand = IoC::resolve('IoC.Scope.Current.Clear');

        $this->tester->assertInstanceOf(ICommand::class, $clearCurrentScopeCommand);

        $clearCurrentScopeCommand->execute();
        $expectRootScope = IoC::resolve('IoC.Scope.Current');

        $this->tester->assertInstanceOf(IoCScope::class, $expectRootScope);
        $this->tester->assertSame($rootScope, $expectRootScope);
        $this->tester->assertNotSame($newScope, $expectRootScope);
    }

    public function testClearEmptyScope(): void
    {
        $rootScope = IoC::resolve('IoC.Scope.Current');

        $clearCurrentScopeCommand = IoC::resolve('IoC.Scope.Current.Clear');

        $this->tester->assertInstanceOf(ICommand::class, $clearCurrentScopeCommand);

        $clearCurrentScopeCommand->execute();
        $expectRootScope = IoC::resolve('IoC.Scope.Current');

        $this->tester->assertInstanceOf(IoCScope::class, $expectRootScope);
        $this->tester->assertSame($rootScope, $expectRootScope);
    }

    public function testFailSetCurrentScopeWithoutParameter(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Первый параметр должен быть экземпляром IoCScope');

        IoC::resolve('IoC.Scope.Current.Set');
    }

    public function testFailSetCurrentScopeWithInvalidParameterType(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Первый параметр должен быть экземпляром IoCScope');

        IoC::resolve('IoC.Scope.Current.Set', 'string');
    }

    public function testRegisterWithScopes(): void
    {
        $firstResisterCommand = IoC::resolve('IoC.Register', 'First.Strategy', static function () {
            return 'First.Strategy';
        });

        $this->tester->assertInstanceOf(ICommand::class, $firstResisterCommand);

        $firstResisterCommand->execute();
        $scope1 = IoC::resolve('IoC.Scope.Create');
        $setScope1Command = IoC::resolve('IoC.Scope.Current.Set', $scope1);
        $setScope1Command->execute();

        $secondResisterCommand = IoC::resolve('IoC.Register', 'Second.Strategy', static function () {
            return 'Second.Strategy';
        });

        $this->tester->assertInstanceOf(ICommand::class, $secondResisterCommand);

        $secondResisterCommand->execute();

        $scope2 = IoC::resolve('IoC.Scope.Create');
        $setScope2Command = IoC::resolve('IoC.Scope.Current.Set', $scope2);
        $setScope2Command->execute();

        $thirdResisterCommand = IoC::resolve('IoC.Register', 'Third.Strategy', static function () {
            return 'Third.Strategy';
        });

        $this->tester->assertInstanceOf(ICommand::class, $thirdResisterCommand);

        $thirdResisterCommand->execute();

        $this->tester->assertEquals('First.Strategy', IoC::resolve('First.Strategy'));
        $this->tester->assertEquals('Second.Strategy', IoC::resolve('Second.Strategy'));
        $this->tester->assertEquals('Third.Strategy', IoC::resolve('Third.Strategy'));
    }

    public function testResolveUnknownDependency(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Неизвестная зависимость IoC: Unknown Dependency');

        IoC::resolve('Unknown Dependency');
    }

    public function testReExecutionInitCommand(): void
    {
        $rootScope = IoC::resolve('IoC.Scope.Current');
        $newScope = IoC::resolve('IoC.Scope.Create');
        $setNewScopeCommand = IoC::resolve('IoC.Scope.Current.Set', $newScope);
        $setNewScopeCommand->execute();

        $curScope1 = IoC::resolve('IoC.Scope.Current');

        $this->tester->assertSame($newScope, $curScope1);
        $this->tester->assertNotSame($rootScope, $curScope1);

        $this->initScopeCommand->execute();
        $curScope2 = IoC::resolve('IoC.Scope.Current');

        $this->tester->assertSame($newScope, $curScope2);
        $this->tester->assertNotSame($rootScope, $curScope2);
    }

    protected function _before(): void
    {
        $this->initScopeCommand = new InitScopeCommand();
        $this->initScopeCommand->execute();
    }
}
