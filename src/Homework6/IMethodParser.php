<?php

namespace App\Homework6;

interface IMethodParser
{
    public function parse(string $methodName): ParsedMethod;
}
