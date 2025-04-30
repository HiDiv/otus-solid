<?php

namespace App\Homework7;

interface ServerThreadInterface
{
    public function run(): void;
    public function hardStop(): void;
    public function softStop(): void;
}
