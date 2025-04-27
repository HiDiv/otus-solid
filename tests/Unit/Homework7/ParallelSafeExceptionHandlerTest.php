<?php

namespace Tests\Unit\Homework7;

use App\Homework3\CommandRepeater;
use App\Homework3\ICommand;
use App\Homework7\DefaultExceptionHandlerStrategy;
use App\Homework7\ParallelSafeExceptionHandler;
use Codeception\Test\Unit;
use Exception;
use Tests\Support\UnitTester;
use Throwable;

class ParallelSafeExceptionHandlerTest extends Unit
{
    protected UnitTester $tester;

//    public function testHandle(): void
//    {
//        $handlerConfig = [
//            ['', '', DefaultExceptionHandlerStrategy::class],
//        ];
//        $sut = new ParallelSafeExceptionHandler($handlerConfig);
//
//        $commandMock = $this->makeEmpty(ICommand::class);
//        $exceptionMock = $this->makeEmpty(Exception::class);
//
//        $result = $sut->handle($commandMock, $exceptionMock);
//
//        $this->assertInstanceOf(CommandRepeater::class, $result);
//    }
}
