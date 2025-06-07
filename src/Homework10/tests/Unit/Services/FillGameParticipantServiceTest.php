<?php

namespace App\Tests\Unit\Services;

use App\Entity\Game;
use App\Entity\User;
use App\Services\FillGameParticipantService;
use App\Services\UserFinderInterface;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;

class FillGameParticipantServiceTest extends Unit
{
    protected UnitTester $tester;
    /** @var MockObject&UserFinderInterface */
    private UserFinderInterface $userFinderMock;

    public function testFillParticipantAddsExistingUsers(): void
    {
        $existingUser1 = new User();
        $existingUser1->setLogin('alice');

        $existingUser2 = new User();
        $existingUser2->setLogin('bob');

        $this->userFinderMock
            ->expects($this->exactly(3))
            ->method('findOneByLogin')
            ->willReturnCallback(function (string $login) use ($existingUser1, $existingUser2) {
                return match ($login) {
                    'alice' => $existingUser1,
                    'bob'   => $existingUser2,
                    default => null,
                };
            });

        $game = new Game();

        $this->tester->assertCount(0, $game->getParticipants());

        $sut = new FillGameParticipantService($this->userFinderMock);

        $sut->fillParticipant($game, ['alice', 'bob', 'charlie']);

        $participants = $game->getParticipants();

        $this->assertCount(2, $participants);

        $logins = [];
        foreach ($participants as $user) {
            $this->assertInstanceOf(User::class, $user);
            $logins[] = $user->getLogin();
        }

        sort($logins);
        $this->assertSame(['alice', 'bob'], $logins);
    }

    public function testFillParticipantDoesNothingWhenNoUsersFound(): void
    {
        $this->userFinderMock
            ->expects($this->exactly(2))
            ->method('findOneByLogin')
            ->willReturn(null);

        $game = new Game();
        $service = new FillGameParticipantService($this->userFinderMock);

        $service->fillParticipant($game, ['eve', 'frank']);

        $this->assertCount(0, $game->getParticipants());
    }

    protected function _before(): void
    {
        $this->userFinderMock = $this->makeEmpty(UserFinderInterface::class);
    }
}
