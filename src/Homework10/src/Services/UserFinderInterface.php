<?php

namespace App\Services;

use App\Entity\User;

interface UserFinderInterface
{
    public function findOneByLogin(string $login): ?User;
}
