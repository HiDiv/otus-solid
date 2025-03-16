<?php

namespace App\Homework6;

use App\Homework2\Vector;

interface IMovable
{
    public function getPosition(): Vector;

    public function setPosition(Vector $position): void;

    public function getVelocity(): Vector;
}
