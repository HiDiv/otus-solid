<?php

namespace Tests\Unit\Homework2;

use App\Homework2\Angle;
use App\Homework2\IRotatingObject;
use App\Homework2\Rotate;
use Codeception\Test\Unit;
use Exception;
use Tests\Support\UnitTester;

class RotateTest extends Unit
{
    protected UnitTester $tester;
    protected $rotatingObjectMock;
    protected Angle $startAngle;
    protected Angle $angularVelocity;
    protected Angle $endAngle;
    protected Rotate $sut;

    public function testSuccessRotate(): void
    {
        $this->rotatingObjectMock->expects(self::once())
            ->method('getAngle')
            ->willReturn($this->startAngle);

        $this->rotatingObjectMock->expects(self::once())
            ->method('getAngularVelocity')
            ->willReturn($this->angularVelocity);

        $this->rotatingObjectMock->expects(self::once())
            ->method('setAngle')
            ->with($this->endAngle);

        $this->sut->execute();
    }

    public function testFailureGetAngle(): void
    {
        $exception = new Exception('Невозможно прочитать начальный угол');
        $this->expectExceptionObject($exception);

        $this->rotatingObjectMock->expects(self::once())
            ->method('getAngle')
            ->willThrowException($exception);

        $this->rotatingObjectMock->expects(self::never())
            ->method('getAngularVelocity')
            ->willReturn($this->angularVelocity);

        $this->rotatingObjectMock->expects(self::never())
            ->method('setAngle')
            ->with($this->endAngle);

        $this->sut->execute();
    }

    public function testFailureGetAngularVelocity(): void
    {
        $exception = new Exception('Невозможно прочитать угловую скорость');
        $this->expectExceptionObject($exception);

        $this->rotatingObjectMock->expects(self::once())
            ->method('getAngle')
            ->willReturn($this->startAngle);

        $this->rotatingObjectMock->expects(self::once())
            ->method('getAngularVelocity')
            ->willThrowException($exception);

        $this->rotatingObjectMock->expects(self::never())
            ->method('setAngle')
            ->with($this->endAngle);

        $this->sut->execute();
    }

    public function testFailureSetAngle(): void
    {
        $exception = new Exception('Невозможно установить конечный угол');
        $this->expectExceptionObject($exception);

        $this->rotatingObjectMock->expects(self::once())
            ->method('getAngle')
            ->willReturn($this->startAngle);

        $this->rotatingObjectMock->expects(self::once())
            ->method('getAngularVelocity')
            ->willReturn($this->angularVelocity);

        $this->rotatingObjectMock->expects(self::once())
            ->method('setAngle')
            ->with($this->endAngle)
            ->willThrowException($exception);

        $this->sut->execute();
    }

    protected function _before(): void
    {
        $this->rotatingObjectMock = $this->createMock(IRotatingObject::class);
        $this->startAngle = new Angle(12, 36);
        $this->angularVelocity = new Angle(7, 36);
        $this->endAngle = new Angle(19, 36);
        $this->sut = new Rotate($this->rotatingObjectMock);
    }
}
