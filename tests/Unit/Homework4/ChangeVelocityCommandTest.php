<?php

namespace Tests\Unit\Homework4;

use App\Homework2\Angle;
use App\Homework4\ChangeVelocityCommand;
use App\Homework4\CommandException;
use App\Homework4\IVelocityChangeable;
use App\Homework4\Velocity;
use Codeception\Test\Unit;
use Tests\Support\UnitTester;

class ChangeVelocityCommandTest extends Unit
{
    protected UnitTester $tester;

    public function testSuccessExecute(): void
    {
        $velocityChangeable = $this->makeEmpty(IVelocityChangeable::class);

        $velocityChangeable->expects($this->once())
            ->method('getVelocity')
            ->willReturn(new Velocity(12, new Angle(10, 36)));

        $velocityChangeable->expects($this->once())
            ->method('getAngle')
            ->willReturn(new Angle(15, 36));

        $velocityChangeable->expects($this->once())
            ->method('setVelocity')
            ->with(new Velocity(12, new Angle(25, 36)));

        $sut = new ChangeVelocityCommand($velocityChangeable);
        $sut->execute();
    }

    public function testFailExecute(): void
    {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(ChangeVelocityCommand::ZERO_MODULE_ERR_MSG);

        $velocityChangeable = $this->makeEmpty(IVelocityChangeable::class);

        $velocityChangeable->expects($this->once())
            ->method('getVelocity')
            ->willReturn(new Velocity(0, new Angle(10, 36)));

        $velocityChangeable->expects($this->never())
            ->method('getAngle')
            ->willReturn(new Angle(15, 36));

        $velocityChangeable->expects($this->never())
            ->method('setVelocity')
            ->with(new Velocity(0, new Angle(25, 36)));

        $sut = new ChangeVelocityCommand($velocityChangeable);
        $sut->execute();
    }
}
