<?php

namespace Tests\Unit\Homework2;

use App\Homework2\Point;
use App\Homework2\Vector;
use Codeception\Test\Unit;
use Tests\Support\UnitTester;

class PointTest extends Unit
{
    protected UnitTester $tester;

    public function testCreatePoint(): void
    {
        $x = -12;
        $y = 7;

        $sut = new Point($x, $y);

        $this->tester->assertEquals($x, $sut->getX());
        $this->tester->assertEquals($y, $sut->getY());
    }

    public function testPointPlusVector(): void
    {
        $vector = new Vector(-7, 3);
        $point = new Point(12, 5);

        $sut = $point->plusVector($vector);

        $this->tester->assertEquals(5, $sut->getX());
        $this->tester->assertEquals(8, $sut->getY());
    }
}
