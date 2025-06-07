<?php

namespace App\Tests\Unit\Services;

use App\Entity\Game;
use App\Exceptions\EmptyGame;
use App\Services\CreateGameService;
use App\Services\FillGameParticipantInterface;
use App\Services\GamePersisterInterface;
use App\Services\GameNotEmptyValidatorInterface;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;

class CreateGameServiceTest extends Unit
{
    protected UnitTester $tester;
    /** @var MockObject&FillGameParticipantInterface */
    private FillGameParticipantInterface $fillGameParticipantMock;
    /** @var MockObject&GameNotEmptyValidatorInterface */
    private GameNotEmptyValidatorInterface $gameValidatorMock;
    /** @var MockObject&GamePersisterInterface */
    private GamePersisterInterface $gamePersisterMock;

    public function testCreateGameOrchestratesAllSteps(): void
    {
        $participantLogins = ['alice', 'bob'];

        $this->fillGameParticipantMock
            ->expects($this->once())
            ->method('fillParticipant')
            ->with(
                $this->isInstanceOf(Game::class),
                $this->equalTo($participantLogins)
            );

        $this->gameValidatorMock
            ->expects($this->once())
            ->method('gameNotEmpty')
            ->with($this->isInstanceOf(Game::class));

        $this->gamePersisterMock
            ->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(Game::class));

        $sut = new CreateGameService(
            $this->fillGameParticipantMock,
            $this->gameValidatorMock,
            $this->gamePersisterMock
        );

        $returnedGame = $sut->createGame($participantLogins);

        $this->assertCount(0, $returnedGame->getParticipants());
    }

    public function testCreateGameDoesNotCallPersisterIfValidatorThrowsException(): void
    {
        $participantLogins = ['charlie'];

        $this->fillGameParticipantMock
            ->expects($this->once())
            ->method('fillParticipant')
            ->with($this->isInstanceOf(Game::class), $participantLogins);

        $errMsg = 'При создании игры не найдено ни одного участника.';
        $this->gameValidatorMock
            ->expects($this->once())
            ->method('gameNotEmpty')
            ->willThrowException(new EmptyGame($errMsg));

        $this->gamePersisterMock
            ->expects($this->never())
            ->method('save');

        $sut = new CreateGameService(
            $this->fillGameParticipantMock,
            $this->gameValidatorMock,
            $this->gamePersisterMock
        );

        $this->expectException(EmptyGame::class);
        $this->expectExceptionMessage($errMsg);

        $sut->createGame($participantLogins);
    }

    protected function _before(): void
    {
        $this->fillGameParticipantMock = $this->makeEmpty(FillGameParticipantInterface::class);
        $this->gameValidatorMock = $this->makeEmpty(GameNotEmptyValidatorInterface::class);
        $this->gamePersisterMock = $this->makeEmpty(GamePersisterInterface::class);
    }
}
