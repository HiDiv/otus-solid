<?php

namespace App\Homework2;

class Point
{
    private int $x;
    private int $y;

    public function __construct(int $x, int $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function plusVector(Vector $vector): Point
    {
        return new Point($this->getX() + $vector->getDx(), $this->getY() + $vector->getDy());
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }
}
