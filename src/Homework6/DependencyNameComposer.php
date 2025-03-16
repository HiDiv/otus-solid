<?php

namespace App\Homework6;

class DependencyNameComposer implements IDependencyNameComposer
{
    public function compose(string $interfaceShortName, ParsedMethod $parsedMethod): string
    {
        $nameParts = [$interfaceShortName];
        if (!empty($parsedMethod->propsName)) {
            $nameParts[] = $parsedMethod->propsName;
        }
        $nameParts[] = $parsedMethod->method;
        return implode(".", $nameParts);
    }
}
