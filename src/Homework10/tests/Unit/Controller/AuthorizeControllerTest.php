<?php

namespace App\Tests\Unit\Controller;

use App\Controller\AuthorizeController;
use App\Entity\User;
use App\Exceptions\ErrorDecodeParams;
use App\Exceptions\UserAccessInGameDenied;
use App\Services\DecodeParamsInterface;
use App\Services\GameAuthorizeInterface;
use App\Services\RequestErrorHandlerInterface;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use LogicException;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class AuthorizeControllerTest extends Unit
{
    protected UnitTester $tester;

    /** @var MockObject&RequestErrorHandlerInterface */
    private RequestErrorHandlerInterface $errorHandler;

    /** @var MockObject&DecodeParamsInterface */
    private DecodeParamsInterface $decodeParams;

    /** @var MockObject&GameAuthorizeInterface */
    private GameAuthorizeInterface $gameAuthorize;

    private User $testUser;

    private AuthorizeController $sut;

    public function testAuthorizeReturnsJsonTokenOnSuccess(): void
    {
        $gameId  = 'test-game-uuid';
        $jsonBody = json_encode(['gameId' => $gameId]);
        $request = new Request([], [], [], [], [], [], $jsonBody);

        $this->decodeParams
            ->expects($this->once())
            ->method('decode')
            ->with($jsonBody)
            ->willReturn(['gameId' => $gameId]);

        $returnedToken = 'new-jwt-token-string';
        $this->gameAuthorize
            ->expects($this->once())
            ->method('authorizeGame')
            ->with($this->testUser, $gameId)
            ->willReturn($returnedToken);

        $this->errorHandler
            ->expects($this->never())
            ->method('handle');

        $response = $this->sut->authorize($request);

        $this->assertEquals(200, $response->getStatusCode());

        $decoded = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('token', $decoded);
        $this->assertSame($returnedToken, $decoded['token']);
    }

    public function testAuthorizeDelegatesToErrorHandlerOnDecodeParamsException(): void
    {
        $request = new Request([], [], [], [], [], [], '{invalid-json:::}');

        $ex = new ErrorDecodeParams('cannot decode');
        $this->decodeParams
            ->expects($this->once())
            ->method('decode')
            ->with('{invalid-json:::}')
            ->willThrowException($ex);

        $this->gameAuthorize
            ->expects($this->never())
            ->method('authorizeGame');

        $fakeResponse = new JsonResponse(['error' => 'handled'], 400);
        $this->errorHandler
            ->expects($this->once())
            ->method('handle')
            ->with($ex, $request)
            ->willReturn($fakeResponse);

        $response = $this->sut->authorize($request);

        $this->assertSame($fakeResponse, $response);
    }

    public function testAuthorizeDelegatesToErrorHandlerOnGameAuthorizeException(): void
    {
        $gameId   = 'some-uuid';
        $jsonBody = json_encode(['gameId' => $gameId]);
        $request  = new Request([], [], [], [], [], [], $jsonBody);

        $this->decodeParams
            ->expects($this->once())
            ->method('decode')
            ->with($jsonBody)
            ->willReturn(['gameId' => $gameId]);

        $ex = new UserAccessInGameDenied('Not allowed');
        $this->gameAuthorize
            ->expects($this->once())
            ->method('authorizeGame')
            ->with($this->testUser, $gameId)
            ->willThrowException($ex);

        $fakeResponse = new JsonResponse(['error' => 'denied'], 403);
        $this->errorHandler
            ->expects($this->once())
            ->method('handle')
            ->with($ex, $request)
            ->willReturn($fakeResponse);

        $response = $this->sut->authorize($request);

        $this->assertSame($fakeResponse, $response);
    }

    protected function _before(): void
    {
        $this->errorHandler = $this->makeEmpty(RequestErrorHandlerInterface::class);
        $this->decodeParams = $this->makeEmpty(DecodeParamsInterface::class);
        $this->gameAuthorize = $this->makeEmpty(GameAuthorizeInterface::class);

        $this->sut = new AuthorizeController(
            $this->errorHandler,
            $this->decodeParams,
            $this->gameAuthorize
        );

        $this->testUser = new User();
        $this->testUser->setLogin('dummy_user');
        $this->testUser->setPassword('irrelevant');

        $token = $this->makeEmpty(TokenInterface::class);
        $token->method('getUser')
            ->willReturn($this->testUser);

        $tokenStorage = $this->makeEmpty(TokenStorageInterface::class);
        $tokenStorage->method('getToken')
            ->willReturn($token);

        $container = $this->makeEmpty(ContainerInterface::class);
        $container->method('get')
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
