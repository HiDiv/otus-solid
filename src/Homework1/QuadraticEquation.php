<?php

namespace App\Homework1;

use RuntimeException;

class QuadraticEquation
{
    private float $e;

    public function __construct(float $e = 10e-5)
    {
        $this->e = $e;
    }

    public function solve(float $a, float $b, float $c): array
    {
        if (abs($a) <= $this->e) {
            throw new RuntimeException('Ошибка: коэффициент A не может быть равен 0.');
        }

        foreach (['A' => $a, 'B' => $b, 'C' => $c] as $arg => $val) {
            if (is_nan($val) || !is_finite($val)) {
                throw new RuntimeException(sprintf('Ошибка: коэффициент %s должен быть числом.', $arg));
            }
        }

        $d = $b * $b - 4 * $a * $c;

        // Корней нет
        if ($d < -$this->e) {
            return [];
        }

        // Один корень
        if (abs($d) <= $this->e) {
            return [-$b / (2 * $a)];
        }

        // Два корня
        $sqrt = sqrt($d);
        $a2 = 2 * $a;
        return [(-$b + $sqrt) / $a2, (-$b - $sqrt) / $a2];
    }
}
