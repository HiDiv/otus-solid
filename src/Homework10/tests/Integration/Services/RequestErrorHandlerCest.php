<?php

namespace App\Tests\Integration\Services;

use App\Exceptions\CommandProcessingError;
use App\Exceptions\EmptyGame;
use App\Exceptions\ErrorDecodeParams;
use App\Exceptions\GameNotFound;
use App\Exceptions\UserAccessInGameDenied;
use App\Services\RequestErrorHandlerInterface;
use App\Tests\Support\IntegrationTester;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

class RequestErrorHandlerCest
{
    public function testHandleUsesEmptyGameStrategy(IntegrationTester $I): void
    {
        $exception = new EmptyGame('no game available');
        $request = new Request();

        $sut = $I->grabService(RequestErrorHandlerInterface::class);

        $response = $sut->handle($exception, $request);

        $I->assertEquals(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        $I->assertArrayHasKey('error', $data);
        $I->assertSame('no game available', $data['error']);
    }

    public function testHandleErrorDecodeParamsStrategy(IntegrationTester $I): void
    {
        $exception = new ErrorDecodeParams('test error');
        $request = new Request();

        $sut = $I->grabService(RequestErrorHandlerInterface::class);

        $response = $sut->handle($exception, $request);

        $I->assertEquals(400, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        $I->assertArrayHasKey('error', $data);
        $I->assertSame('test error', $data['error']);
    }

    public function testHandleGameNotFoundStrategy(IntegrationTester $I): void
    {
        $exception = new GameNotFound('test game error');
        $request = new Request();

        $sut = $I->grabService(RequestErrorHandlerInterface::class);

        $response = $sut->handle($exception, $request);

        $I->assertEquals(404, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        $I->assertArrayHasKey('error', $data);
        $I->assertSame('test game error', $data['error']);
    }

    public function testHandleUserAccessInGameDeniedStrategy(IntegrationTester $I): void
    {
        $exception = new UserAccessInGameDenied('not allowed');
        $request = new Request();

        $sut = $I->grabService(RequestErrorHandlerInterface::class);

        $response = $sut->handle($exception, $request);

        $I->assertEquals(403, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        $I->assertArrayHasKey('error', $data);
        $I->assertSame('not allowed', $data['error']);
    }

    public function testHandleCommandProcessingStrategy(IntegrationTester $I): void
    {
        $exception = new CommandProcessingError('same command error');
        $request = new Request();

        $sut = $I->grabService(RequestErrorHandlerInterface::class);

        $response = $sut->handle($exception, $request);

        $I->assertEquals(422, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        $I->assertArrayHasKey('error', $data);
        $I->assertSame('same command error', $data['error']);
    }

    public function testHandleUsesDefaultStrategyWhenNoSpecific(IntegrationTester $I): void
    {
        $exception = new RuntimeException('oops');
        $request   = new Request();

        $sut = $I->grabService(RequestErrorHandlerInterface::class);

        $response = $sut->handle($exception, $request);

        $I->assertEquals(500, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        $I->assertArrayHasKey('error', $data);
        $I->assertSame('oops', $data['error']);
    }
}
