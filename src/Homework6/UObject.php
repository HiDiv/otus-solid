<?php

namespace App\Homework6;

interface UObject
{
    public function getProperty(string $propsName): mixed;

    public function setProperty(string $propsName, mixed $value): void;
}
