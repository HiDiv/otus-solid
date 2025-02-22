<?php

namespace App\Homework4;

use App\Homework2\Angle;

interface IVelocityChangeable
{
    public function getVelocity(): Velocity;

    public function getAngle(): Angle;

    public function setVelocity(Velocity $newVelocity): void;
}
