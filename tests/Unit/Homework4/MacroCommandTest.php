<?php

namespace Tests\Unit\Homework4;

use App\Homework3\ICommand;
use App\Homework4\CommandException;
use App\Homework4\MacroCommand;
use Codeception\Test\Unit;
use Exception;
use Tests\Support\UnitTester;

class MacroCommandTest extends Unit
{
    protected UnitTester $tester;

    public function testSuccessExecute(): void
    {
        $expectOrder = [];

        $command1 = $this->makeEmpty(ICommand::class, ['execute' => function () use (&$expectOrder) {
            $expectOrder[] = 'command1';
        }]);

        $command2 = $this->makeEmpty(ICommand::class, ['execute' => function () use (&$expectOrder) {
            $expectOrder[] = 'command2';
        }]);

        $sut = new MacroCommand([$command1, $command2]);
        $sut->execute();

        $this->tester->assertEquals(['command1', 'command2'], $expectOrder);
    }

    public function testFailExecute(): void
    {
        $exceptMsg = 'test exception';
        $exception = new Exception($exceptMsg);

        $this->expectException(CommandException::class);
        $this->expectExceptionMessage($exceptMsg);

        $command1 = $this->makeEmpty(ICommand::class);
        $command1->expects($this->once())->method('execute')->with();

        $command2 = $this->makeEmpty(ICommand::class);
        $command2->expects($this->once())
            ->method('execute')
            ->willThrowException($exception);

        $command3 = $this->makeEmpty(ICommand::class);
        $command3->expects($this->never())->method('execute')->with();;

        $sut = new MacroCommand([$command1, $command2, $command3]);
        $sut->execute();
    }
}
