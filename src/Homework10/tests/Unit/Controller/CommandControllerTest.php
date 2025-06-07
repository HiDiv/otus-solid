<?php

namespace App\Tests\Unit\Controller;

use App\Controller\CommandController;
use App\Entity\Game;
use App\Entity\User;
use App\Exceptions\CommandProcessingError;
use App\Exceptions\ErrorDecodeParams;
use App\Exceptions\GameNotFound;
use App\Exceptions\UserAccessInGameDenied;
use App\Services\CommandProcessorInterface;
use App\Services\DecodeParamsInterface;
use App\Services\GameByIdFetcherInterface;
use App\Services\GameIdExtractorInterface;
use App\Services\RequestErrorHandlerInterface;
use App\Services\UserRegisteredInGameInterface;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class CommandControllerTest extends Unit
{
    protected UnitTester $tester;
    /** @var MockObject&RequestErrorHandlerInterface */
    private $errorHandler;

    /** @var MockObject&GameIdExtractorInterface */
    private $gameIdExtractor;

    /** @var MockObject&DecodeParamsInterface */
    private $decodeParams;

    /** @var MockObject&GameByIdFetcherInterface */
    private $gameByIdFetcher;

    /** @var MockObject&UserRegisteredInGameInterface */
    private $userRegisteredInGame;

    /** @var MockObject&CommandProcessorInterface */
    private $commandProcessor;

    private CommandController $sut;
    private User $testUser;

    public function testCommandReturnsJsonOnSuccess(): void
    {
        $gameId = 'game-uuid-123';
        $rawJson = json_encode(['command' => ['action' => 'move']]);
        $request = new Request([], [], [], [], [], [], $rawJson);

        $this->gameIdExtractor
            ->expects($this->once())
            ->method('extract')
            ->with($request)
            ->willReturn($gameId);

        $decoded = ['command' => ['action' => 'move']];
        $this->decodeParams
            ->expects($this->once())
            ->method('decode')
            ->with($rawJson)
            ->willReturn($decoded);

        $game = new Game();
        $this->gameByIdFetcher
            ->expects($this->once())
            ->method('fetch')
            ->with($gameId)
            ->willReturn($game);

        $this->userRegisteredInGame
            ->expects($this->once())
            ->method('checkAccess')
            ->with($this->testUser, $game);

        $this->commandProcessor
            ->expects($this->once())
            ->method('process')
            ->with($this->testUser, $game, ['action' => 'move']);

        $this->errorHandler
            ->expects($this->never())
            ->method('handle');

        $response = $this->sut->command($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertSame(['status' => 'Command accepted'], json_decode($response->getContent(), true));
    }

    public function testCommandDelegatesToErrorHandlerOnGameIdExtractionException(): void
    {
        $request = new Request();

        $ex = new LogicException('bad header');
        $this->gameIdExtractor
            ->expects($this->once())
            ->method('extract')
            ->with($request)
            ->willThrowException($ex);

        $this->decodeParams->expects($this->never())->method('decode');
        $this->gameByIdFetcher->expects($this->never())->method('fetch');
        $this->userRegisteredInGame->expects($this->never())->method('checkAccess');
        $this->commandProcessor->expects($this->never())->method('process');

        $fakeResponse = new JsonResponse(['error' => 'handled'], 400);
        $this->errorHandler
            ->expects($this->once())
            ->method('handle')
            ->with($ex, $request)
            ->willReturn($fakeResponse);

        $response = $this->sut->command($request);

        $this->assertSame($fakeResponse, $response);
    }

    public function testCommandDelegatesToErrorHandlerOnDecodeParamsException(): void
    {
        $gameId = 'game-uuid';
        $rawJson = '{bad-json:::}';
        $request = new Request([], [], [], [], [], [], $rawJson);

        $this->gameIdExtractor
            ->expects($this->once())
            ->method('extract')
            ->with($request)
            ->willReturn($gameId);

        $ex = new ErrorDecodeParams('cannot decode');
        $this->decodeParams
            ->expects($this->once())
            ->method('decode')
            ->with($rawJson)
            ->willThrowException($ex);

        $this->gameByIdFetcher->expects($this->never())->method('fetch');
        $this->userRegisteredInGame->expects($this->never())->method('checkAccess');
        $this->commandProcessor->expects($this->never())->method('process');

        $fakeResponse = new JsonResponse(['error' => 'decode failed'], 400);
        $this->errorHandler
            ->expects($this->once())
            ->method('handle')
            ->with($ex, $request)
            ->willReturn($fakeResponse);

        $response = $this->sut->command($request);

        $this->assertSame($fakeResponse, $response);
    }

    public function testCommandDelegatesToErrorHandlerOnGameFetchException(): void
    {
        $gameId = 'game-not-exist';
        $rawJson = json_encode(['command' => ['action' => 'test']]);
        $request = new Request([], [], [], [], [], [], $rawJson);

        $this->gameIdExtractor
            ->expects($this->once())
            ->method('extract')
            ->with($request)
            ->willReturn($gameId);

        $this->decodeParams
            ->expects($this->once())
            ->method('decode')
            ->with($rawJson)
            ->willReturn(['command' => ['action' => 'test']]);

        $ex = new GameNotFound('Game not found');
        $this->gameByIdFetcher
            ->expects($this->once())
            ->method('fetch')
            ->with($gameId)
            ->willThrowException($ex);

        $this->userRegisteredInGame->expects($this->never())->method('checkAccess');
        $this->commandProcessor->expects($this->never())->method('process');

        $fakeResponse = new JsonResponse(['error' => 'game missing'], 404);
        $this->errorHandler
            ->expects($this->once())
            ->method('handle')
            ->with($ex, $request)
            ->willReturn($fakeResponse);

        $response = $this->sut->command($request);

        $this->assertSame($fakeResponse, $response);
    }

    public function testCommandDelegatesToErrorHandlerOnAccessDeniedException(): void
    {
        $gameId = 'game-uuid';
        $rawJson = json_encode(['command' => ['action' => 'do']]);
        $request = new Request([], [], [], [], [], [], $rawJson);

        $this->gameIdExtractor
            ->expects($this->once())
            ->method('extract')
            ->with($request)
            ->willReturn($gameId);

        $this->decodeParams
            ->expects($this->once())
            ->method('decode')
            ->with($rawJson)
            ->willReturn(['command' => ['action' => 'do']]);

        $game = new Game();
        $this->gameByIdFetcher
            ->expects($this->once())
            ->method('fetch')
            ->with($gameId)
            ->willReturn($game);

        $ex = new UserAccessInGameDenied('Not allowed');
        $this->userRegisteredInGame
            ->expects($this->once())
            ->method('checkAccess')
            ->with($this->testUser, $game)
            ->willThrowException($ex);

        $this->commandProcessor->expects($this->never())->method('process');

        $fakeResponse = new JsonResponse(['error' => 'denied'], 403);
        $this->errorHandler
            ->expects($this->once())
            ->method('handle')
            ->with($ex, $request)
            ->willReturn($fakeResponse);

        $response = $this->sut->command($request);

        $this->assertSame($fakeResponse, $response);
    }

    public function testCommandDelegatesToErrorHandlerOnCommandProcessingException(): void
    {
        $gameId = 'game-uuid';
        $rawJson = json_encode(['command' => ['action' => 'invalid']]);
        $request = new Request([], [], [], [], [], [], $rawJson);

        $this->gameIdExtractor
            ->expects($this->once())
            ->method('extract')
            ->with($request)
            ->willReturn($gameId);

        $this->decodeParams
            ->expects($this->once())
            ->method('decode')
            ->with($rawJson)
            ->willReturn(['command' => ['action' => 'invalid']]);

        $game = new Game();
        $this->gameByIdFetcher
            ->expects($this->once())
            ->method('fetch')
            ->with($gameId)
            ->willReturn($game);

        $this->userRegisteredInGame
            ->expects($this->once())
            ->method('checkAccess')
            ->with($this->testUser, $game);

        $ex = new CommandProcessingError('Invalid command');
        $this->commandProcessor
            ->expects($this->once())
            ->method('process')
            ->with($this->testUser, $game, ['action' => 'invalid'])
            ->willThrowException($ex);

        $fakeResponse = new JsonResponse(['error' => 'Invalid command'], 422);
        $this->errorHandler
            ->expects($this->once())
            ->method('handle')
            ->with($ex, $request)
            ->willReturn($fakeResponse);

        $response = $this->sut->command($request);

        $this->assertSame($fakeResponse, $response);
    }

    protected function _before(): void
    {
        $this->errorHandler = $this->makeEmpty(RequestErrorHandlerInterface::class);
        $this->gameIdExtractor = $this->makeEmpty(GameIdExtractorInterface::class);
        $this->decodeParams = $this->makeEmpty(DecodeParamsInterface::class);
        $this->gameByIdFetcher = $this->makeEmpty(GameByIdFetcherInterface::class);
        $this->userRegisteredInGame = $this->makeEmpty(UserRegisteredInGameInterface::class);
        $this->commandProcessor = $this->makeEmpty(CommandProcessorInterface::class);

        $this->sut = new CommandController(
            $this->errorHandler,
            $this->gameIdExtractor,
            $this->decodeParams,
            $this->gameByIdFetcher,
            $this->userRegisteredInGame,
            $this->commandProcessor
        );

        $this->testUser = new User();
        $this->testUser->setLogin('dummy');

        $token = $this->makeEmpty(TokenInterface::class);
        $token->method('getUser')->willReturn($this->testUser);

        $tokenStorage = $this->makeEmpty(TokenStorageInterface::class);
        $tokenStorage->method('getToken')->willReturn($token);

        $container = $this->makeEmpty(ContainerInterface::class);
        $container
            ->method('get')
            ->with('security.token_storage')
            ->willReturn($tokenStorage);

        $container->method('has')
            ->willReturnMap([
                ['security.token_storage', true],
                ['serializer', false],
            ]);

        $this->sut->setContainer($container);
    }
}
