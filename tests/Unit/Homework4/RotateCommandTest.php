<?php

namespace Tests\Unit\Homework4;

use App\Homework2\Angle;
use App\Homework2\IRotatingObject;
use App\Homework4\CommandException;
use App\Homework4\RotateCommand;
use Codeception\Test\Unit;
use Tests\Support\UnitTester;

class RotateCommandTest extends Unit
{
    protected UnitTester $tester;

    public function testSuccessExecute(): void
    {
        $rotatingObject = $this->makeEmpty(IRotatingObject::class);

        $rotatingObject->expects($this->once())
            ->method('getAngle')
            ->willReturn(new Angle(120, 360));

        $rotatingObject->expects($this->once())
            ->method('getAngularVelocity')
            ->willReturn(new Angle(240, 360));

        $rotatingObject->expects($this->once())
            ->method('setAngle')
            ->with(new Angle(0, 360));

        $sut = new RotateCommand($rotatingObject);
        $sut->execute();
    }

    public function testFailExecute(): void
    {
        $this->expectException(CommandException::class);

        $rotatingObject = $this->makeEmpty(IRotatingObject::class);

        $rotatingObject->expects($this->once())
            ->method('getAngle')
            ->willReturn(new Angle(120, 360));

        $rotatingObject->expects($this->once())
            ->method('getAngularVelocity')
            ->willReturn(new Angle(24, 36));

        $rotatingObject->expects($this->never())
            ->method('setAngle')
            ->with(new Angle(0, 360));

        $sut = new RotateCommand($rotatingObject);
        $sut->execute();
    }
}
