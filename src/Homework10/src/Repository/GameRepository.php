<?php

namespace App\Repository;

use App\Entity\Game;
use App\Services\GameFinderInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use App\Services\GamePersisterInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Uid\Uuid;

class GameRepository extends ServiceEntityRepository implements GameFinderInterface, GamePersisterInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function save(Game $game): void
    {
        $em = $this->getEntityManager();
        $em->persist($game);
        $em->flush();
    }

    public function findById(Uuid $id): ?Game
    {
        return $this->find($id);
    }
}
