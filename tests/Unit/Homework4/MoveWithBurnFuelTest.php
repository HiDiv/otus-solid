<?php

namespace Tests\Unit\Homework4;

use App\Homework4\BurnFuelCommand;
use App\Homework4\CheckFuelCommand;
use App\Homework4\CommandException;
use App\Homework4\MacroCommand;
use App\Homework4\MoveCommand;
use Codeception\Stub\Expected;
use Codeception\Test\Unit;
use RuntimeException;
use Tests\Support\UnitTester;

class MoveWithBurnFuelTest extends Unit
{
    protected UnitTester $tester;

    public function testSuccessMove(): void
    {
        $expectOrder = [];

        $checkFuelCommand = $this->make(
            CheckFuelCommand::class,
            [
                'execute' => function () use (&$expectOrder) {
                    $expectOrder[] = 'CheckFuelCommand';
                },
            ]
        );

        $burnFuelCommand = $this->make(
            BurnFuelCommand::class,
            [
                'execute' => function () use (&$expectOrder) {
                    $expectOrder[] = 'BurnFuelCommand';
                },
            ]
        );

        $moveCommand = $this->make(
            MoveCommand::class,
            [
                'execute' => function () use (&$expectOrder) {
                    $expectOrder[] = 'MoveCommand';
                },
            ]
        );

        $sut = new MacroCommand([$checkFuelCommand, $burnFuelCommand, $moveCommand]);
        $sut->execute();

        $this->tester->assertEquals(['CheckFuelCommand', 'BurnFuelCommand', 'MoveCommand'], $expectOrder);
    }

    public function testFailMove(): void
    {
        $exceptionMsg = 'test exception';
        $this->expectExceptionMessage($exceptionMsg);
        $this->expectException(CommandException::class);

        $checkFuelCommand = $this->make(CheckFuelCommand::class, ['execute' => Expected::once()]);

        $burnFuelCommand = $this->make(
            BurnFuelCommand::class,
            [
                'execute' => function () use ($exceptionMsg) {
                    throw new RuntimeException($exceptionMsg);
                },
            ]
        );

        $moveCommand = $this->make(MoveCommand::class, ['execute' => Expected::never()]);

        $sut = new MacroCommand([$checkFuelCommand, $burnFuelCommand, $moveCommand]);
        $sut->execute();
    }
}
