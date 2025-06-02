<?php

namespace App\Tests\Unit\Services;

use App\Exceptions\ErrorDecodeParams;
use App\Services\DecodeParamsService;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;

class DecodeParamsServiceTest extends Unit
{
    protected UnitTester $tester;
    protected DecodeParamsService $sut;

    public function testSomeFeature(): void
    {
        $expected = ['foo' => 'bar'];

        $result = $this->sut->decode('{"foo":"bar"}');

        $this->tester->assertEquals($expected, $result);
    }

    public function testErrorDecode(): void
    {
        $this->tester->expectThrowable(
            new ErrorDecodeParams('Error decode json params: Syntax error'),
            function () {
                $this->sut->decode('ddd');
            }
        );
    }

    public function testErrorEmptyParams(): void
    {
        $this->tester->expectThrowable(
            new ErrorDecodeParams('Params must not be empty array'),
            function () {
                $this->sut->decode('[]');
            }
        );
    }

    protected function _before(): void
    {
        $this->sut = new DecodeParamsService();
    }
}
