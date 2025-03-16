<?php

namespace App\Homework6;

interface IDependencyNameComposer
{
    public function compose(string $interfaceShortName, ParsedMethod $parsedMethod): string;
}
