<?php

namespace App\Tests\Functional\Controller;

use App\Entity\Game;
use App\Entity\User;
use App\Repository\GameRepository;
use App\Repository\UserRepository;
use App\Tests\Support\FunctionalTester;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\Uid\Uuid;

class AuthorizeControllerCest
{
    private string $jwtToken;
    private Uuid $gameId;

    public function _before(FunctionalTester $I): void
    {
        /** @var EntityManagerInterface $em */
        $em = $I->grabService(EntityManagerInterface::class);

        $mainUser = new User();
        $mainUser->setLogin('main_user');
        $mainUser->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $em->persist($mainUser);

        $gamer1 = new User();
        $gamer1->setLogin('gamer1');
        $gamer1->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $em->persist($gamer1);

        $gamer2 = new User();
        $gamer2->setLogin('gamer2');
        $gamer2->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $em->persist($gamer2);

        $em->flush();

        $game = new Game();
        $game->addParticipant($gamer1);
        $em->persist($game);
        $em->flush();

        $this->gameId = $game->getId();

        /** @var JWTTokenManagerInterface $jwtManager */
        $jwtManager = $I->grabService(JWTTokenManagerInterface::class);
        $this->jwtToken = $jwtManager->create($mainUser);
    }

    public function testAuthorizeSuccess(FunctionalTester $I): void
    {
        /** @var EntityManagerInterface $em */
        $em = $I->grabService(EntityManagerInterface::class);
        /** @var GameRepository $gr */
        $gr = $I->grabService(GameRepository::class);
        /** @var UserRepository $ur */
        $ur = $I->grabService(UserRepository::class);

        $mainUser = $ur->findOneByLogin('main_user');
        $game = $gr->findById($this->gameId);
        $game->addParticipant($mainUser);
        $em->flush();
        $em->clear();

        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->jwtToken);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/authorize', json_encode(['gameId' => $this->gameId->toRfc4122()]));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseJsonMatchesJsonPath('$.token');
        $newToken = $I->grabDataFromResponseByJsonPath('$.token')[0];
        $I->assertNotEmpty($newToken);

        /** @var JWTTokenManagerInterface $jwtManager */
        $jwtManager = $I->grabService(JWTTokenManagerInterface::class);
        $payload = $jwtManager->parse($newToken);
        $I->assertArrayHasKey('game', $payload);
        $I->assertEquals($this->gameId->toRfc4122(), $payload['game']);
    }

    public function testAuthorizeUnauthenticatedReturns401(FunctionalTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/authorize', json_encode(['gameId' => $this->gameId->toRfc4122()]));
        $I->seeResponseCodeIs(401);
    }

    public function testAuthorizeUserNotInGameReturns403(FunctionalTester $I): void
    {
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->jwtToken);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/authorize', json_encode(['gameId' => $this->gameId->toRfc4122()]));

        $I->seeResponseCodeIs(403);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['error' => 'The user is not registered in the game']);
    }

    public function testAuthorizeBadJsonHandledByErrorHandler(FunctionalTester $I): void
    {
        $I->haveHttpHeader('Authorization', 'Bearer ' . $this->jwtToken);
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/authorize', '{invalid-json:::}');

        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['error' => 'Error decode json params: Syntax error']);
    }
}
