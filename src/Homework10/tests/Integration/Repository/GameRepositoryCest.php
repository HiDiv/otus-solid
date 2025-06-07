<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Game;
use App\Repository\GameRepository;
use App\Tests\Support\IntegrationTester;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class GameRepositoryCest
{
    public function testSaveAndFindById(IntegrationTester $I): void
    {
        /** @var GameRepository $gameRepo */
        $gameRepo = $I->grabService(GameRepository::class);

        $game = new Game();

        $gameRepo->save($game);

        $I->assertNotNull($game->getId());

        $generatedId = $game->getId();

        $I->assertInstanceOf(Uuid::class, $generatedId);

        /** @var EntityManagerInterface $em */
        $em = $I->grabService(EntityManagerInterface::class);
        $em->clear();

        $loaded = $gameRepo->findById($generatedId);
        $I->assertInstanceOf(Game::class, $loaded);
        $I->assertSame($loaded->getId()->toRfc4122(), $generatedId->toRfc4122());

        $I->assertCount(0, $loaded->getParticipants());
    }

    public function testFindByIdReturnsNullForNonExisting(IntegrationTester $I): void
    {
        /** @var GameRepository $gameRepo */
        $repo = $I->grabService(GameRepository::class);

        $randomId = Uuid::v4();

        $notFound = $repo->findById($randomId);

        $I->assertNull($notFound);
    }
}
