<?php

namespace Tests\Unit\Homework5;

use App\Homework5\IoCScope;
use Codeception\Test\Unit;
use Tests\Support\UnitTester;
use ValueError;

class IoCScopeTest extends Unit
{
    protected UnitTester $tester;
    protected IoCScope $sut;

    public function testSuccessStrategyResolve(): void
    {
        $testFunc = function () {
        };

        $this->sut->addStrategy('Test Dependence', $testFunc);

        $this->tester->assertTrue($this->sut->hasStrategy('Test Dependence'));
        $this->tester->assertSame($testFunc, $this->sut->getStrategy('Test Dependence'));
    }

    public function testCheckUnknownDependence(): void
    {
        $this->tester->assertFalse($this->sut->hasStrategy('Test Dependence'));
    }

    public function testFailGetUnknownDependence(): void
    {
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('Неизвестная зависимость IoC: Test Dependence');

        $this->sut->getStrategy('Test Dependence');
    }

    protected function _before(): void
    {
        $this->sut = new IoCScope();
    }
}
