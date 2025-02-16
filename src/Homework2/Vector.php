<?php

namespace App\Homework2;

class Vector
{
    private int $dx;
    private int $dy;

    public function __construct(int $dx, int $dy)
    {
        $this->dx = $dx;
        $this->dy = $dy;
    }

    public function getDx(): int
    {
        return $this->dx;
    }

    public function getDy(): int
    {
        return $this->dy;
    }
}
