<?php

namespace App\Tests\Unit\Services;

use App\Entity\Game;
use App\Entity\User;
use App\Exceptions\ErrorDecodeParams;
use App\Exceptions\GameNotFound;
use App\Exceptions\UserAccessInGameDenied;
use App\Services\CreateTokenForGameInterface;
use App\Services\GameAuthorizeService;
use App\Services\GameByIdFetcherInterface;
use App\Services\UserRegisteredInGameInterface;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Uid\Uuid;

class GameAuthorizeServiceTest extends Unit
{
    protected UnitTester $tester;

    /** @var MockObject&GameByIdFetcherInterface */
    private GameByIdFetcherInterface $gameByIdFetcherMock;

    /** @var MockObject&UserRegisteredInGameInterface */
    private UserRegisteredInGameInterface $userRegisteredInGameMock;

    /** @var MockObject&CreateTokenForGameInterface */
    private CreateTokenForGameInterface $createTokenForGameMock;

    public function testAuthorizeGameReturnsTokenWhenAllDependenciesSucceed(): void
    {
        $user = new User();
        $user->setLogin('test_user');
        $gameUuid = Uuid::v4();
        $gameIdStr = $gameUuid->toRfc4122();

        $game = $this->make(Game::class, ['id' => $gameUuid]);

        $this->gameByIdFetcherMock
            ->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo($gameIdStr))
            ->willReturn($game);

        $this->userRegisteredInGameMock
            ->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo($user), $this->equalTo($game));

        $dummyToken = 'dummy.jwt.token';
        $this->createTokenForGameMock
            ->expects($this->once())
            ->method('createToken')
            ->with($this->equalTo($user), $this->equalTo($gameUuid))
            ->willReturn($dummyToken);

        $sut = new GameAuthorizeService(
            $this->gameByIdFetcherMock,
            $this->userRegisteredInGameMock,
            $this->createTokenForGameMock
        );

        $returnedToken = $sut->authorizeGame($user, $gameIdStr);

        $this->assertSame($dummyToken, $returnedToken);
    }

    public function testAuthorizeGamePropagatesErrorDecodeParamsFromFetcher(): void
    {
        $user = new User();
        $badIdStr = '';

        $this->gameByIdFetcherMock
            ->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo($badIdStr))
            ->willThrowException(new ErrorDecodeParams('gameId is required'));

        $this->userRegisteredInGameMock
            ->expects($this->never())
            ->method('checkAccess');

        $this->createTokenForGameMock
            ->expects($this->never())
            ->method('createToken');

        $sut = new GameAuthorizeService(
            $this->gameByIdFetcherMock,
            $this->userRegisteredInGameMock,
            $this->createTokenForGameMock
        );

        $this->expectException(ErrorDecodeParams::class);
        $this->expectExceptionMessage('gameId is required');

        $sut->authorizeGame($user, $badIdStr);
    }

    public function testAuthorizeGamePropagatesGameNotFoundFromFetcher(): void
    {
        $user = new User();
        $missingIdStr = Uuid::v4()->toRfc4122();

        $this->gameByIdFetcherMock
            ->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo($missingIdStr))
            ->willThrowException(new GameNotFound('Game not found'));

        $this->userRegisteredInGameMock
            ->expects($this->never())
            ->method('checkAccess');

        $this->createTokenForGameMock
            ->expects($this->never())
            ->method('createToken');

        $sut = new GameAuthorizeService(
            $this->gameByIdFetcherMock,
            $this->userRegisteredInGameMock,
            $this->createTokenForGameMock
        );

        $this->expectException(GameNotFound::class);
        $this->expectExceptionMessage('Game not found');

        $sut->authorizeGame($user, $missingIdStr);
    }

    public function testAuthorizeGamePropagatesAccessDeniedFromUserCheck(): void
    {
        $user = new User();
        $gameUuid = Uuid::v4();
        $gameIdStr = $gameUuid->toRfc4122();

        $game = $this->make(Game::class, ['id' => $gameUuid]);

        $this->gameByIdFetcherMock
            ->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo($gameIdStr))
            ->willReturn($game);

        $this->userRegisteredInGameMock
            ->expects($this->once())
            ->method('checkAccess')
            ->with($this->equalTo($user), $this->equalTo($game))
            ->willThrowException(new UserAccessInGameDenied('Access denied for this game'));

        $this->createTokenForGameMock
            ->expects($this->never())
            ->method('createToken');

        $sut = new GameAuthorizeService(
            $this->gameByIdFetcherMock,
            $this->userRegisteredInGameMock,
            $this->createTokenForGameMock
        );

        $this->expectException(UserAccessInGameDenied::class);
        $this->expectExceptionMessage('Access denied for this game');

        $sut->authorizeGame($user, $gameIdStr);
    }

    protected function _before(): void
    {
        $this->gameByIdFetcherMock = $this->makeEmpty(GameByIdFetcherInterface::class);
        $this->userRegisteredInGameMock = $this->makeEmpty(UserRegisteredInGameInterface::class);
        $this->createTokenForGameMock = $this->makeEmpty(CreateTokenForGameInterface::class);
    }
}
