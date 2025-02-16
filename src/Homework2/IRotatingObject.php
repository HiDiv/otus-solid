<?php

namespace App\Homework2;

interface IRotatingObject
{
    public function getAngle(): Angle;

    public function getAngularVelocity(): Angle;

    public function setAngle(Angle $newAngle): void;
}
