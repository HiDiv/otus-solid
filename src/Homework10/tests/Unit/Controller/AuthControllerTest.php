<?php

namespace App\Tests\Unit\Controller;

use App\Controller\AuthController;
use App\Entity\Game;
use App\Services\CreateGameInterface;
use App\Services\DecodeParamsInterface;
use App\Services\RequestErrorHandlerInterface;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Uid\Uuid;

class AuthControllerTest extends Unit
{
    protected UnitTester $tester;

    /** @var MockObject&RequestErrorHandlerInterface */
    private $errorHandler;

    /** @var MockObject&DecodeParamsInterface */
    private $decodeParams;

    /** @var MockObject&CreateGameInterface */
    private $createGameService;

    /** @var AuthController */
    private $sut;

    public function testCreateGameReturnsJsonOnSuccess(): void
    {
        $requestContent = json_encode(['participants' => ['alice', 'bob']]);
        $request = new Request([], [], [], [], [], [], $requestContent);

        $decoded = ['participants' => ['alice', 'bob']];
        $this->decodeParams
            ->expects($this->once())
            ->method('decode')
            ->with($requestContent)
            ->willReturn($decoded);

        $uuid = Uuid::v4();
        $game = $this->make(Game::class, ['id' => $uuid]);

        $this->createGameService
            ->expects($this->once())
            ->method('createGame')
            ->with($decoded['participants'])
            ->willReturn($game);

        $this->errorHandler
            ->expects($this->never())
            ->method('handle');

        $response = $this->sut->createGame($request);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('gameId', $data);
        $this->assertEquals($uuid->toRfc4122(), $data['gameId']);
    }

    public function testCreateGameDelegatesToErrorHandlerOnException(): void
    {
        $requestContent = '{"bad": "payload"}';
        $request = new Request([], [], [], [], [], [], $requestContent);

        $exception = new RuntimeException('invalid JSON');
        $this->decodeParams
            ->expects($this->once())
            ->method('decode')
            ->with($requestContent)
            ->willThrowException($exception);

        $this->createGameService
            ->expects($this->never())
            ->method('createGame');

        $fakeResponse = new JsonResponse(['error' => 'handled'], 400);
        $this->errorHandler
            ->expects($this->once())
            ->method('handle')
            ->with($exception, $request)
            ->willReturn($fakeResponse);

        $response = $this->sut->createGame($request);

        $this->assertSame($fakeResponse, $response);
    }

    protected function _before(): void
    {
        $this->errorHandler = $this->makeEmpty(RequestErrorHandlerInterface::class);
        $this->decodeParams = $this->makeEmpty(DecodeParamsInterface::class);
        $this->createGameService = $this->makeEmpty(CreateGameInterface::class);

        $this->sut = new AuthController(
            $this->errorHandler,
            $this->decodeParams,
            $this->createGameService
        );

        $dummyContainer = $this->makeEmpty(ContainerInterface::class);
        $this->sut->setContainer($dummyContainer);
    }
}
