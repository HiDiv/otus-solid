<?php

namespace App\Homework6;

class CamelCaseMethodParser implements IMethodParser
{
    public function parse(string $methodName): ParsedMethod
    {
        if (preg_match('/^([a-z]+)([A-Z][a-zA-Z0-9]*)$/', $methodName, $matches)) {
            return new ParsedMethod(ucfirst($matches[1]), $matches[2]);
        }

        return new ParsedMethod(ucfirst($methodName), '');
    }
}
