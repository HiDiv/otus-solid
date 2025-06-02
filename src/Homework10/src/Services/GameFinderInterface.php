<?php

namespace App\Services;

use App\Entity\Game;
use Symfony\Component\Uid\Uuid;

interface GameFinderInterface
{
    public function findById(Uuid $id): ?Game;
}
