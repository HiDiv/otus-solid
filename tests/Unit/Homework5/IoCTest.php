<?php

namespace Tests\Unit\Homework5;

use App\Homework3\ICommand;
use App\Homework5\IoC;
use Codeception\Test\Unit;
use Tests\Support\UnitTester;
use ValueError;

class IoCTest extends Unit
{
    protected UnitTester $tester;

    public function testUnknownDependency(): void
    {
        $strategyUpdater = static function () {
            return null;
        };

        $result = IoC::resolve('Update Ioc Resolve Dependency Strategy', $strategyUpdater);

        $this->tester->assertInstanceOf(ICommand::class, $result);

        $this->tester->expectThrowable(
            new ValueError('Неизвестная зависимость IoC: Unknown Dependency'),
            function () use ($result) {
                $result->execute();
                IoC::resolve('Unknown Dependency', 'first', 'second');
            }
        );
    }

    public function testErrorGettingUpdateDependencyStrategyCommandWithoutParameter(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Первый параметр должен быть экземпляром Closure');

        IoC::resolve('Update Ioc Resolve Dependency Strategy');
    }

    public function testErrorGettingUpdateDependencyStrategyCommandWithInvalidParameterType(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Первый параметр должен быть экземпляром Closure');

        IoC::resolve('Update Ioc Resolve Dependency Strategy', 'string');
    }

    public function testUpdateNewStrategy(): void
    {
        $strategy = static function (string $dependency, ...$params) {
            return sprintf('dependency: %s, params: %s', $dependency, implode(', ', $params));
        };
        $updater = static function () use ($strategy) {
            return $strategy;
        };

        /** @var ICommand $command */
        $command = IoC::resolve('Update Ioc Resolve Dependency Strategy', $updater);
        $command->execute();
        $result = IoC::resolve('Test.Dependency', 'first', 'second');

        $this->tester->assertEquals('dependency: Test.Dependency, params: first, second', $result);
    }
}
