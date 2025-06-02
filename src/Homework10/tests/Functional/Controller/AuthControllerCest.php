<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Repository\GameRepository;
use App\Tests\Support\FunctionalTester;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Uid\Uuid;

class AuthControllerCest
{
    private string $jwtToken;

    public function _before(FunctionalTester $I): void
    {
        /** @var EntityManagerInterface $em */
        $em = $I->grabService(EntityManagerInterface::class);

        $user = new User();
        $user->setLogin('test_user');
        $user->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $em->persist($user);

        $alice = new User();
        $alice->setLogin('alice');
        $alice->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $em->persist($alice);

        $bob = new User();
        $bob->setLogin('bob');
        $bob->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $em->persist($bob);

        $em->flush();
        $jwtManager = $I->grabService(JWTTokenManagerInterface::class);
        $this->jwtToken = $jwtManager->create($user);
    }

    public function testCreateGameSuccess(FunctionalTester $I): void
    {
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->jwtToken);

        $I->haveHttpHeader('Content-Type', 'application/json');

        $payloadArray = ['participants' => ['alice', 'bob']];
        $jsonBody = json_encode($payloadArray);

        $I->sendPOST('/create-game', $jsonBody);

        $I->seeResponseCodeIs(200);

        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('$.gameId');
        $gameId = $I->grabDataFromResponseByJsonPath('$.gameId')[0];
        $I->assertTrue(Uuid::isValid($gameId));

        /** @var GameRepository $gameRepo */
        $gameRepo = $I->grabService(GameRepository::class);
        $savedGame = $gameRepo->findById(Uuid::fromString($gameId));
        $I->assertNotNull($savedGame);
        $participants = $savedGame->getParticipants()->map(fn ($u) => $u->getLogin())->getValues();
        sort($participants);
        $I->assertEquals(['alice', 'bob'], $participants);
    }

    public function testCreateGameUnauthenticatedReturns401(FunctionalTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/create-game', json_encode(['participants' => ['x', 'y']]));
        $I->seeResponseCodeIs(401);
    }

    public function testCreateGameBadJsonHandledByErrorHandler(FunctionalTester $I): void
    {
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->jwtToken);

        $I->haveHttpHeader('Content-Type', 'application/json');
        $invalidJson = '{invalid-json:::}';
        $I->sendPOST('/create-game', $invalidJson);

        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['error' => 'Error decode json params: Syntax error']);
    }
}
