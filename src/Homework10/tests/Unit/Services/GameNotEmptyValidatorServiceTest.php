<?php

namespace App\Tests\Unit\Services;

use App\Entity\Game;
use App\Entity\User;
use App\Exceptions\EmptyGame;
use App\Services\GameNotEmptyValidatorService;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;

class GameNotEmptyValidatorServiceTest extends Unit
{
    protected UnitTester $tester;

    public function testSuccessGame(): void
    {
        $user = new User();
        $user->setLogin('test-user');

        $game = new Game();
        $game->addParticipant($user);

        $sut = new GameNotEmptyValidatorService();

        $sut->gameNotEmpty($game);

        $this->tester->assertTrue(true);
    }

    public function testEmptyGame(): void
    {
        $game = new Game();

        $sut = new GameNotEmptyValidatorService();

        $this->expectException(EmptyGame::class);
        $this->expectExceptionMessage('No participants found while creating the game');

        $sut->gameNotEmpty($game);
    }
}
