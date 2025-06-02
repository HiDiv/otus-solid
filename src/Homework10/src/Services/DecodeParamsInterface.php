<?php

namespace App\Services;

interface DecodeParamsInterface
{
    public function decode(string $jsonStr): array;
}
