<?php

namespace Tests\Unit\Homework2;

use App\Homework2\IMovingObject;
use App\Homework2\Move;
use App\Homework2\Point;
use App\Homework2\Vector;
use Codeception\Test\Unit;
use Exception;
use Tests\Support\UnitTester;

class MoveTest extends Unit
{
    protected UnitTester $tester;
    protected $movingObjectMock;
    protected Point $startPoint;
    protected Vector $vector;
    protected Point $endPoint;
    protected Move $sut;

    public function testSuccessMove(): void
    {
        $this->movingObjectMock->expects($this->once())
            ->method('getLocation')
            ->willReturn($this->startPoint);

        $this->movingObjectMock->expects($this->once())
            ->method('getVelocity')
            ->willReturn($this->vector);

        $this->movingObjectMock->expects($this->once())
            ->method('setLocation')
            ->with($this->endPoint);

        $this->sut->execute();
    }

    public function testFailureGetLocation(): void
    {
        $exception = new Exception('Невозможно прочитать положение в пространстве');

        $this->movingObjectMock->expects($this->once())
            ->method('getLocation')
            ->willThrowException($exception);

        $this->movingObjectMock->expects($this->never())
            ->method('getVelocity')
            ->willReturn($this->vector);

        $this->movingObjectMock->expects($this->never())
            ->method('setLocation')
            ->with($this->endPoint);

        $this->tester->expectThrowable(
            $exception,
            function () {
                $this->sut->execute();
            }
        );
    }

    public function testFailureGetVelocity(): void
    {
        $exception = new Exception('Невозможно прочитать значение мгновенной скорости');

        $this->movingObjectMock->expects($this->once())
            ->method('getLocation')
            ->willReturn($this->startPoint);

        $this->movingObjectMock->expects($this->once())
            ->method('getVelocity')
            ->willThrowException($exception);

        $this->movingObjectMock->expects($this->never())
            ->method('setLocation')
            ->with($this->endPoint);

        $this->tester->expectThrowable(
            $exception,
            function () {
                $this->sut->execute();
            }
        );
    }

    public function testFailureSetLocation(): void
    {
        $exception = new Exception('Невозможно изменить положение в пространстве');

        $this->movingObjectMock->expects($this->once())
            ->method('getLocation')
            ->willReturn($this->startPoint);

        $this->movingObjectMock->expects($this->once())
            ->method('getVelocity')
            ->willReturn($this->vector);

        $this->movingObjectMock->expects($this->once())
            ->method('setLocation')
            ->willThrowException($exception);

        $this->tester->expectThrowable(
            $exception,
            function () {
                $this->sut->execute();
            }
        );
    }

    protected function _before(): void
    {
        $this->startPoint = new Point(12, 5);
        $this->vector = new Vector(-7, 3);
        $this->endPoint = new Point(5, 8);
        $this->movingObjectMock = $this->createMock(IMovingObject::class);
        $this->sut = new Move($this->movingObjectMock);
    }
}
