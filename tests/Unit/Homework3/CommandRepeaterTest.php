<?php

namespace Tests\Unit\Homework3;

use App\Homework3\CommandRepeater;
use App\Homework3\ICommand;
use Codeception\Test\Unit;
use Tests\Support\UnitTester;

class CommandRepeaterTest extends Unit
{
    protected UnitTester $tester;

    public function testExecute(): void
    {
        $repeatableCommand = $this->makeEmpty(ICommand::class);
        $repeatableCommand->expects(self::once())->method('execute');

        $sut = new CommandRepeater($repeatableCommand);
        $sut->execute();
    }
}
