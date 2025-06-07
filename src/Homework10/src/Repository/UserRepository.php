<?php

namespace App\Repository;

use App\Entity\User;
use App\Services\UserFinderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class UserRepository extends ServiceEntityRepository implements UserFinderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findOneByLogin(string $login): ?User
    {
        return $this->findOneBy(['login' => $login]);
    }
}
