<?php

namespace App\Homework4;

interface IFuelObject
{
    public function getFuelVolume(): int;

    public function getOneTimeVolume(): int;

    public function setFuelVolume(int $newVolume): void;
}
