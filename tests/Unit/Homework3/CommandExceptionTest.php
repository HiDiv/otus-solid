<?php

namespace Tests\Unit\Homework3;

use App\Homework3\CommandException;
use Codeception\Test\Unit;
use Exception;
use Tests\Support\UnitTester;

class CommandExceptionTest extends Unit
{
    protected UnitTester $tester;

    public function testExecute(): void
    {
        $exception = new Exception('test');
        $this->expectExceptionObject($exception);

        $sut = new CommandException($exception);
        $sut->execute();
    }
}
