<?php

namespace Tests\Unit\Homework1;

use App\Homework1\QuadraticEquation;
use Codeception\Test\Unit;
use RuntimeException;
use Tests\Support\UnitTester;

class QuadraticEquationTest extends Unit
{
    protected UnitTester $tester;
    protected float $e;
    protected QuadraticEquation $sut;

    public static function solveOkDataProvider(): array
    {
        return [
            'Корней нет' => ['a' => 1.000001, 'b' => 0.000001, 'c' => 1.000001, 'expect' => []],
            'Два корня кратности 1' => ['a' => 1.000001, 'b' => 0.000001, 'c' => -1.000001, 'expect' => [1, -1]],
            'Один корень кратности 2, D == 0' => ['a' => 1, 'b' => 2, 'c' => 1, 'expect' => [-1]],
            'Один корень кратности 2, abs(D) < e' => ['a' => 1.000001, 'b' => -2.000001, 'c' => 1.000001, 'expect' => [1]],
        ];
    }

    public static function solveErrorDataProvider(): array
    {
        return [
            'Коэффициент A не может быть равен 0' => [
                'a' => 0.000001,
                'b' => 1,
                'c' => 1,
                'expect' => 'Ошибка: коэффициент A не может быть равен 0.',
            ],
            'Коэффициент A не является числом' => [
                'a' => NAN,
                'b' => 1,
                'c' => 1,
                'expect' => 'Ошибка: коэффициент A должен быть числом.',
            ],
            'Коэффициент A бесконечность' => [
                'a' => INF,
                'b' => 1,
                'c' => 1,
                'expect' => 'Ошибка: коэффициент A должен быть числом.',
            ],
            'Коэффициент B не является числом' => [
                'a' => 1,
                'b' => NAN,
                'c' => 1,
                'expect' => 'Ошибка: коэффициент B должен быть числом.',
            ],
            'Коэффициент B бесконечность' => [
                'a' => 1,
                'b' => INF,
                'c' => 1,
                'expect' => 'Ошибка: коэффициент B должен быть числом.',
            ],
            'Коэффициент C не является числом' => [
                'a' => 1,
                'b' => 1,
                'c' => NAN,
                'expect' => 'Ошибка: коэффициент C должен быть числом.',
            ],
            'Коэффициент C бесконечность' => [
                'a' => 1,
                'b' => 1,
                'c' => INF,
                'expect' => 'Ошибка: коэффициент C должен быть числом.',
            ],
        ];
    }

    /**
     * @param float $a
     * @param float $b
     * @param float $c
     * @param float[] $expect
     * @dataProvider solveOkDataProvider
     */
    public function testSolveOk(float $a, float $b, float $c, array $expect): void
    {
        $result = $this->sut->solve($a, $b, $c);

        $this->tester->assertEqualsWithDelta($expect, $result, $this->e);
    }

    /**
     * @param float $a
     * @param float $b
     * @param float $c
     * @param string $expect
     * @dataProvider solveErrorDataProvider
     */
    public function testSolveError(float $a, float $b, float $c, string $expect): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage($expect);

        $this->sut->solve($a, $b, $c);
    }

    protected function _before(): void
    {
        $this->e = 10e-5;
        $this->sut = new QuadraticEquation($this->e);
    }
}
