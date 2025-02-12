<?php

namespace App\Homework2;

interface IMovingObject
{
    public function getLocation(): Point;

    public function getVelocity(): Vector;

    public function setLocation(Point $newLocation): void;
}
