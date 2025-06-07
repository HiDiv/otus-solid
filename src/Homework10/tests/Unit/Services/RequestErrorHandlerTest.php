<?php

namespace App\Tests\Unit\Services;

use App\Exceptions\EmptyGame;
use App\Services\ErrorHandlerStrategyInterface;
use App\Services\RequestErrorHandler;
use App\Tests\Support\UnitTester;
use Codeception\Test\Unit;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Service\ServiceProviderInterface;

class RequestErrorHandlerTest extends Unit
{
    protected UnitTester $tester;
    protected RequestErrorHandler $sut;
    /** @var MockObject&ServiceProviderInterface */
    private $handlersContainer;

    public function testHandleCallsSpecificStrategyWhenHandlerExistsForExceptionClass(): void
    {
        $exception = new EmptyGame('game not found');
        $request = new Request();

        $this->handlersContainer
            ->expects($this->once())
            ->method('has')
            ->willReturnCallback(static function (string $key) {
                return $key === EmptyGame::class;
            });

        /** @var MockObject&ErrorHandlerStrategyInterface $specificStrategy */
        $specificStrategy = $this->makeEmpty(ErrorHandlerStrategyInterface::class);

        $this->handlersContainer
            ->expects($this->once())
            ->method('get')
            ->with(EmptyGame::class)
            ->willReturn($specificStrategy);

        $expectedResponse = new JsonResponse(['error' => 'game not found'], 400);
        $specificStrategy
            ->expects($this->once())
            ->method('__invoke')
            ->with($exception, $request)
            ->willReturn($expectedResponse);

        $response = $this->sut->handle($exception, $request);

        $this->assertSame($expectedResponse, $response);
        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testHandleCallsDefaultStrategyWhenNoSpecificButDefaultExists(): void
    {
        $exception = new RuntimeException('something went wrong');
        $request   = new Request();

        $this->handlersContainer
            ->expects($this->atLeastOnce())
            ->method('has')
            ->willReturnCallback(static function (string $key) {
                return $key === RequestErrorHandler::DEFAULT_STRATEGY;
            });

        /** @var MockObject&ErrorHandlerStrategyInterface $defaultStrategy */
        $defaultStrategy = $this->makeEmpty(ErrorHandlerStrategyInterface::class);

        $this->handlersContainer
            ->expects($this->once())
            ->method('get')
            ->with(RequestErrorHandler::DEFAULT_STRATEGY)
            ->willReturn($defaultStrategy);

        $expectedResponse = new JsonResponse(['error' => 'something went wrong'], 500);
        $defaultStrategy
            ->expects($this->once())
            ->method('__invoke')
            ->with($exception, $request)
            ->willReturn($expectedResponse);

        $response = $this->sut->handle($exception, $request);

        $this->assertSame($expectedResponse, $response);
        $this->assertEquals(500, $response->getStatusCode());
    }

    public function testHandleRethrowsExceptionWhenNoStrategyFound(): void
    {
        $exception = new Exception('unknown error');
        $request   = new Request();

        $this->handlersContainer
            ->expects($this->exactly(2))
            ->method('has')
            ->willReturn(false);

        $this->handlersContainer
            ->expects($this->never())
            ->method('get');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('unknown error');

        $this->sut->handle($exception, $request);
    }

    protected function _before(): void
    {
        $this->handlersContainer = $this->makeEmpty(ServiceProviderInterface::class);
        $this->sut = new RequestErrorHandler($this->handlersContainer);
    }
}
