<?php

namespace App\Homework6;

/**
 * Для тестирования адаптера на нестандартных методах
 */
interface IMovableExt extends IMovable
{
    public function normalize($number = '123'): void;

    public function finish();
}
