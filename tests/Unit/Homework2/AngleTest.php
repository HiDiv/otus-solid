<?php

namespace Tests\Unit\Homework2;

use App\Homework2\Angle;
use Codeception\Test\Unit;
use Exception;
use Tests\Support\UnitTester;

class AngleTest extends Unit
{
    protected UnitTester $tester;

    public static function failCreateDataProvider(): array
    {
        return [
            'd равно n' => ['d' => 10, 'n' => 10],
            'd больше n' => ['d' => 37, 'n' => 36],
        ];
    }

    public static function successAnglePlusDataProvider(): array
    {
        return [
            'd1 + d2 меньше n' => ['d1' => 10, 'd2' => 5, 'expect' => 15],
            'd1 + d2 больше n' => ['d1' => 35, 'd2' => 3, 'expect' => 2],
        ];
    }

    public function testSuccessCreateAngle(): void
    {
        $d = 5;
        $n = 36;

        $sut = new Angle($d, $n);

        $this->tester->assertEquals($d, $sut->getD());
        $this->tester->assertEquals($n, $sut->getN());
    }

    /**
     * @param int $d
     * @param int $n
     * @@dataProvider failCreateDataProvider
     */
    public function testFailCreateAngle(int $d, int $n): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('d must be less than n');

        new Angle($d, $n);
    }

    /**
     * @param int $d1
     * @param int $d2
     * @param int $expect
     * @@dataProvider successAnglePlusDataProvider
     * @throws Exception
     */
    public function testSuccessAnglePlus(int $d1, int $d2, int $expect): void
    {
        $angle1 = new Angle($d1, 36);
        $angle2 = new Angle($d2, 36);

        $sut = $angle1->plus($angle2);

        $this->tester->assertEquals($expect, $sut->getD());
    }

    public function testFailAnglePlus(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Operands Angle must have same division');

        $angle = new Angle(10, 24);
        $sut = new Angle(5, 36);

        $sut->plus($angle);
    }
}
