<?php

namespace Tests\Unit\Homework6;

use App\Homework6\DependencyNameComposer;
use App\Homework6\ParsedMethod;
use Codeception\Test\Unit;
use Tests\Support\UnitTester;

class DependencyNameComposerTest extends Unit
{
    protected UnitTester $tester;
    protected DependencyNameComposer $sut;

    public function testMethodWithProps(): void
    {
        $result = $this->sut->compose('IMovable', new ParsedMethod('Get', 'Position'));

        $this->tester->assertEquals('IMovable.Position.Get', $result);
    }

    public function testMethodWithoutProps(): void
    {
        $result = $this->sut->compose('IMovable', new ParsedMethod('Finish', ''));

        $this->tester->assertEquals('IMovable.Finish', $result);
    }

    protected function _before(): void
    {
        $this->sut = new DependencyNameComposer();
    }
}
