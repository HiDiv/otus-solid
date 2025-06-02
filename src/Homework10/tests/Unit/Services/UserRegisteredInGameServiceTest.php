<?php

namespace App\Tests\Unit\Services;

use App\Entity\Game;
use App\Entity\User;
use App\Exceptions\UserAccessInGameDenied;
use App\Services\UserRegisteredInGameService;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;

class UserRegisteredInGameServiceTest extends Unit
{
    protected UnitTester $tester;

    private UserRegisteredInGameService $sut;

    public function testCheckAccessDoesNotThrowWhenUserIsParticipant(): void
    {
        $user = new User();
        $user->setLogin('participant_user');

        $game = new Game();
        $game->addParticipant($user);

        $this->sut->checkAccess($user, $game);

        $this->assertTrue(true);
    }

    public function testCheckAccessThrowsAccessDeniedWhenUserNotParticipant(): void
    {
        $user = new User();
        $user->setLogin('stranger_user');

        $anotherUser = new User();
        $anotherUser->setLogin('other_participant');

        $game = new Game();
        $game->addParticipant($anotherUser);

        $this->expectException(UserAccessInGameDenied::class);
        $this->expectExceptionMessage('The user is not registered in the game');

        $this->sut->checkAccess($user, $game);
    }

    protected function _before(): void
    {
        $this->sut = new UserRegisteredInGameService();
    }
}
