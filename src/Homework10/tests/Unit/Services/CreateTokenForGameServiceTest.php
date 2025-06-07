<?php

namespace App\Tests\Unit\Services;

use App\Entity\User;
use App\Services\CreateTokenForGameService;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Uid\Uuid;

class CreateTokenForGameServiceTest extends Unit
{
    protected UnitTester $tester;

    /** @var MockObject&JWTTokenManagerInterface */
    private JWTTokenManagerInterface $jwtManagerMock;

    public function testCreateTokenCallsJwtManagerWithCorrectPayload(): void
    {
        $user = new User();
        $user->setLogin('gamer_user');
        $user->setRoles(['ROLE_PLAYER', 'ROLE_USER']);

        $gameUuid = Uuid::v4();

        $dummyToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.dummy.payload';

        $this->jwtManagerMock
            ->expects($this->once())
            ->method('createFromPayload')
            ->with(
                $this->equalTo($user),
                $this->callback(function (array $payload) use ($user, $gameUuid) {
                    if (!isset($payload['username'], $payload['roles'], $payload['game'])) {
                        return false;
                    }

                    if ($payload['username'] !== $user->getUserIdentifier()) {
                        return false;
                    }

                    if ($payload['roles'] !== $user->getRoles()) {
                        return false;
                    }

                    if (!$payload['game'] instanceof Uuid) {
                        return false;
                    }
                    return $payload['game']->toRfc4122() === $gameUuid->toRfc4122();
                })
            )
            ->willReturn($dummyToken);

        $service = new CreateTokenForGameService($this->jwtManagerMock);

        $returnedToken = $service->createToken($user, $gameUuid);

        $this->assertSame($dummyToken, $returnedToken);
    }

    protected function _before(): void
    {
        $this->jwtManagerMock = $this->makeEmpty(JWTTokenManagerInterface::class);
    }
}
