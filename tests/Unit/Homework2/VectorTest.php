<?php

namespace Tests\Unit\Homework2;

use App\Homework2\Vector;
use Codeception\Test\Unit;
use Tests\Support\UnitTester;

class VectorTest extends Unit
{
    protected UnitTester $tester;

    public function testCreateVector(): void
    {
        $dx = 5;
        $dy = -2;

        $sut = new Vector($dx, $dy);

        $this->tester->assertEquals($dx, $sut->getDx());
        $this->tester->assertEquals($dy, $sut->getDy());
    }
}
