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

class CommandControllerCest
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

    public function testCommandSuccess(FunctionalTester $I): void
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

        /** @var JWTTokenManagerInterface $jwtManager */
        $jwtManager = $I->grabService(JWTTokenManagerInterface::class);
        $newJwtForCommand = $jwtManager->createFromPayload(
            $mainUser,
            [
                'username' => $mainUser->getUserIdentifier(),
                'roles'    => $mainUser->getRoles(),
                'game'     => $this->gameId,
            ]
        );

        $I->haveHttpHeader('Authorization', 'Bearer ' . $newJwtForCommand);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $payloadArray = [
            'command' => [
                'action'    => 'move',
                'direction' => 'north',
                'steps'     => 3,
            ],
        ];
        $I->sendPOST('/command', json_encode($payloadArray));

        $I->seeResponseCodeIs(200);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['status' => 'Command accepted']);
    }

    public function testCommandUnauthenticatedReturns401(FunctionalTester $I): void
    {
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPOST('/command', json_encode(['command' => ['action' => 'jump']]));
        $I->seeResponseCodeIs(401);
    }

    public function testCommandUserNotInGameReturns403(FunctionalTester $I): void
    {
        /** @var UserRepository $ur */
        $ur = $I->grabService(UserRepository::class);
        $mainUser = $ur->findOneByLogin('main_user');
        /** @var JWTTokenManagerInterface $jwtManager */
        $jwtManager = $I->grabService(JWTTokenManagerInterface::class);
        $badJwt = $jwtManager->createFromPayload(
            $mainUser,
            [
                'username' => $mainUser->getUserIdentifier(),
                'roles'    => $mainUser->getRoles(),
                'game'     => $this->gameId,
            ]
        );

        $I->haveHttpHeader('Authorization', 'Bearer ' . $badJwt);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/command', json_encode(['command' => ['action' => 'attack']]));

        $I->seeResponseCodeIs(403);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['error' => 'The user is not registered in the game']);
    }

    public function testCommandBadJsonHandledByErrorHandler(FunctionalTester $I): void
    {
        /** @var UserRepository $ur */
        $ur = $I->grabService(UserRepository::class);
        $mainUser = $ur->findOneByLogin('main_user');
        /** @var JWTTokenManagerInterface $jwtManager */
        $jwtManager = $I->grabService(JWTTokenManagerInterface::class);
        $jwtString = $jwtManager->createFromPayload(
            $mainUser,
            [
                'username' => $mainUser->getUserIdentifier(),
                'roles'    => $mainUser->getRoles(),
                'game'     => $this->gameId,
            ]
        );

        $I->haveHttpHeader('Authorization', 'Bearer ' . $jwtString);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/command', '{bad-json:::}');
        $I->seeResponseCodeIs(400);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['error' => 'Error decode json params: Syntax error']);
    }

    public function testCommandInvalidParamsHandledByCommandProcessor(FunctionalTester $I): void
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

        /** @var JWTTokenManagerInterface $jwtManager */
        $jwtManager = $I->grabService(JWTTokenManagerInterface::class);
        $correctJwt = $jwtManager->createFromPayload(
            $mainUser,
            [
                'username' => $mainUser->getUserIdentifier(),
                'roles'    => $mainUser->getRoles(),
                'game'     => $this->gameId,
            ]
        );

        $I->haveHttpHeader('Authorization', 'Bearer ' . $correctJwt);
        $I->haveHttpHeader('Content-Type', 'application/json');

        $I->sendPOST('/command', json_encode(['command' => []]));

        $I->seeResponseCodeIs(422);
        $I->seeResponseIsJson();
        $I->seeResponseContainsJson(['error' => 'Command parameters cannot be empty.']);
    }
}
