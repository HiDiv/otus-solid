<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Throwable;

interface RequestErrorHandlerInterface
{
    public function handle(Throwable $exception, Request $req): JsonResponse;
}
