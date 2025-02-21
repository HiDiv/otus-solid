<?php

namespace App\Homework4;

use App\Homework2\Angle;
use App\Homework2\Vector;

class Velocity
{
    private int $module;
    private Angle $angle;

    public function __construct(int $module, Angle $angle)
    {
        $this->module = $module;
        $this->angle = $angle;
    }

    public function getModule(): int
    {
        return $this->module;
    }

    public function getAngle(): Angle
    {
        return $this->angle;
    }
}
