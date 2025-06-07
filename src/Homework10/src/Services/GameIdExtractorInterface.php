<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\Request;

interface GameIdExtractorInterface
{
    public function extract(Request $req): string;
}
