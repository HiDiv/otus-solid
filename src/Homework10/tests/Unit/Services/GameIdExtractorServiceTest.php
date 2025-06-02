<?php

namespace App\Tests\Unit\Services;

use App\Exceptions\GameNotFound;
use App\Services\GameIdExtractorService;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

class GameIdExtractorServiceTest extends Unit
{
    protected UnitTester $tester;

    /** @var MockObject&JWTTokenManagerInterface */
    private $jwtManager;

    private GameIdExtractorService $sut;

    public function testExtractReturnsGameIdWhenJwtContainsGame(): void
    {
        $gameId = 'abc123-uuid';
        $payload = ['game' => $gameId];
        $jwtString = 'valid.jwt.token';

        $request = new Request();
        $request->headers->set('Authorization', 'Bearer ' . $jwtString);

        $this->jwtManager
            ->expects($this->once())
            ->method('parse')
            ->with($jwtString)
            ->willReturn($payload);

        $result = $this->sut->extract($request);

        $this->assertSame($gameId, $result);
    }

    public function testExtractThrowsLogicExceptionWhenHeaderMissingOrMalformed(): void
    {
        $request1 = new Request();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Missing or malformed Authorization header');

        $this->sut->extract($request1);

        $request2 = new Request();
        $request2->headers->set('Authorization', 'Token something');

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Missing or malformed Authorization header');
        $this->sut->extract($request2);
    }

    public function testExtractThrowsGameNotFoundWhenPayloadLacksGameKey(): void
    {
        $jwtString = 'some.jwt.token';
        $request = new Request();
        $request->headers->set('Authorization', 'Bearer ' . $jwtString);

        $this->jwtManager
            ->expects($this->once())
            ->method('parse')
            ->with($jwtString)
            ->willReturn(['user' => 'test']);

        $this->expectException(GameNotFound::class);
        $this->expectExceptionMessage('JWT payload does not contain game ID');

        $this->sut->extract($request);
    }

    protected function _before(): void
    {
        $this->jwtManager = $this->makeEmpty(JWTTokenManagerInterface::class);
        $this->sut = new GameIdExtractorService($this->jwtManager);
    }
}
