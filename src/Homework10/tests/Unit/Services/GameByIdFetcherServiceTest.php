<?php

namespace App\Tests\Unit\Services;

use App\Entity\Game;
use App\Exceptions\ErrorDecodeParams;
use App\Exceptions\GameNotFound;
use App\Services\GameByIdFetcherService;
use App\Services\GameFinderInterface;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Uid\Uuid;

class GameByIdFetcherServiceTest extends Unit
{
    protected UnitTester $tester;

    /** @var MockObject&GameFinderInterface */
    private GameFinderInterface $gameFinderMock;

    public function testFetchThrowsErrorDecodeParamsWhenEmpty(): void
    {
        $sut = new GameByIdFetcherService($this->gameFinderMock);

        $this->expectException(ErrorDecodeParams::class);
        $this->expectExceptionMessage('gameId is required');

        $sut->fetch('');
    }

    public function testFetchThrowsErrorWhenInvalidUuidFormat(): void
    {
        $sut = new GameByIdFetcherService($this->gameFinderMock);

        $this->expectException(ErrorDecodeParams::class);
        $this->expectExceptionMessage('gameId is not a valid UUID');

        $sut->fetch('not-a-uuid');
    }

    public function testFetchThrowsGameNotFoundWhenNoGame(): void
    {
        $uuidString = Uuid::v4()->toRfc4122();
        $uuidObject = Uuid::fromString($uuidString);

        $this->gameFinderMock
            ->expects($this->once())
            ->method('findById')
            ->with($this->callback(function (Uuid $u) use ($uuidObject) {
                return $u->toRfc4122() === $uuidObject->toRfc4122();
            }))
            ->willReturn(null);

        $sut = new GameByIdFetcherService($this->gameFinderMock);

        $this->expectException(GameNotFound::class);
        $this->expectExceptionMessage('Game not found');

        $sut->fetch($uuidString);
    }

    public function testFetchReturnsGameWhenExists(): void
    {
        $generatedUuid = Uuid::v4();
        $game = $this->make(Game::class, ['id' => $generatedUuid]);

        $uuidString = $generatedUuid->toRfc4122();
        $uuidObject = Uuid::fromString($uuidString);

        $this->gameFinderMock
            ->expects($this->once())
            ->method('findById')
            ->with($this->equalTo($uuidObject))
            ->willReturn($game);

        $sut = new GameByIdFetcherService($this->gameFinderMock);

        $returnedGame = $sut->fetch($uuidString);

        $this->assertSame($game, $returnedGame);
        $this->assertEquals($generatedUuid->toRfc4122(), $returnedGame->getId()->toRfc4122());
    }

    protected function _before(): void
    {
        $this->gameFinderMock = $this->makeEmpty(GameFinderInterface::class);
    }
}
