<?php

namespace Tests\Unit\Homework4;

use App\Homework4\ChangeVelocityCommand;
use App\Homework4\MacroCommand;
use App\Homework4\RotateCommand;
use Codeception\Test\Unit;
use Tests\Support\UnitTester;

class RotateWithVelocityChange extends Unit
{
    protected UnitTester $tester;

    public function testSuccessMove(): void
    {
        $expectOrder = [];

        $rotateCommand = $this->make(
            RotateCommand::class,
            [
                'execute' => function () use (&$expectOrder) {
                    $expectOrder[] = 'RotateCommand';
                },
            ]
        );

        $changeVelocityCommand = $this->make(
            ChangeVelocityCommand::class,
            [
                'execute' => function () use (&$expectOrder) {
                    $expectOrder[] = 'ChangeVelocityCommand';
                },
            ]
        );

        $sut = new MacroCommand([$rotateCommand, $changeVelocityCommand]);
        $sut->execute();

        $this->tester->assertEquals(['RotateCommand', 'ChangeVelocityCommand'], $expectOrder);
    }
}
