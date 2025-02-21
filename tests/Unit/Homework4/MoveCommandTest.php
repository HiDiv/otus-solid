<?php

namespace Tests\Unit\Homework4;

use App\Homework2\IMovingObject;
use App\Homework2\Point;
use App\Homework2\Vector;
use App\Homework4\CommandException;
use App\Homework4\MoveCommand;
use Codeception\Test\Unit;
use Tests\Support\UnitTester;

class MoveCommandTest extends Unit
{
    protected UnitTester $tester;

    public function testSuccessExecute(): void
    {
        $movingObject = $this->makeEmpty(IMovingObject::class);

        $movingObject->expects($this->once())
            ->method('getLocation')
            ->willReturn(new Point(12, 5));

        $movingObject->expects($this->once())
            ->method('getVelocity')
            ->willReturn(new Vector(-7, 3));

        $movingObject->expects($this->once())
            ->method('setLocation')
            ->with(new Point(5, 8));

        $sut = new MoveCommand($movingObject);
        $sut->execute();
    }

    public function testFailExecute(): void
    {
        $this->expectException(CommandException::class);
        $this->expectExceptionMessage(MoveCommand::ZERO_VELOCITY_ERR_MSG);

        $movingObject = $this->makeEmpty(IMovingObject::class);

        $movingObject->expects($this->never())
            ->method('getLocation')
            ->willReturn(new Point(12, 5));

        $movingObject->expects($this->once())
            ->method('getVelocity')
            ->willReturn(new Vector(0, 0));

        $movingObject->expects($this->never())
            ->method('setLocation')
            ->with(new Point(12, 5));

        $sut = new MoveCommand($movingObject);
        $sut->execute();
    }
}
