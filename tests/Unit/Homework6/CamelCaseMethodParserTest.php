<?php

namespace Tests\Unit\Homework6;

use App\Homework6\CamelCaseMethodParser;
use App\Homework6\ParsedMethod;
use Codeception\Test\Unit;
use Tests\Support\UnitTester;

class CamelCaseMethodParserTest extends Unit
{
    protected UnitTester $tester;

    public static function parseDataProvider(): array
    {
        return [
            'Есть название метода и поля' => [
                'methodName' => 'getLongName',
                'expect' => new ParsedMethod('Get', 'LongName'),
            ],
            'Есть только название метода в нижнем регистре' => [
                'methodName' => 'finish',
                'expect' => new ParsedMethod('Finish', ''),
            ],
            'Есть только название метода в верхнем регистре' => [
                'methodName' => 'StartLongMove',
                'expect' => new ParsedMethod('StartLongMove', ''),
            ],
        ];
    }

    /**
     * @param string $methodName
     * @param ParsedMethod $expect
     * @dataProvider parseDataProvider
     */
    public function testParse(string $methodName, ParsedMethod $expect): void
    {
        $parser = new CamelCaseMethodParser();

        $result = $parser->parse($methodName);

        $this->tester->assertEquals($expect, $result);
    }
}
