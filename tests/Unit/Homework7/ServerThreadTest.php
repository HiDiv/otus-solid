<?php

namespace Tests\Unit\Homework7;

use App\Homework3\CommandException;
use App\Homework3\ExceptionHandler;
use App\Homework7\EmptyCommand;
use App\Homework7\HardStopException;
use App\Homework7\HardStopExceptionCommand;
use App\Homework7\HardStopServerCommand;
use App\Homework7\ReceiverInterface;
use App\Homework7\ServerThread;
use App\Homework7\SoftStopException;
use App\Homework7\SoftStopExceptionCommand;
use App\Homework7\SoftStopServerCommand;
use Codeception\Test\Unit;
use Exception;
use RuntimeException;
use Tests\Support\UnitTester;

class ServerThreadTest extends Unit
{
    protected UnitTester $tester;
    protected array $queue;
    protected ExceptionHandler $exceptionHandler;
    protected ServerThread $sut;

    public function _before(): void
    {
        $this->queue = [];
        $receiver = $this->makeEmpty(ReceiverInterface::class);
        $receiver
            ->expects($this->atLeastOnce())
            ->method('receive')
            ->willReturnCallback(
                function () {
                    if (count($this->queue) > 0) {
                        return array_shift($this->queue);
                    }
                    throw new RuntimeException('Очередь пуста');
                }
            );
        $receiver
            ->expects($this->any())
            ->method('empty')
            ->willReturnCallback(function () {
                return count($this->queue) === 0;
            });

        $this->exceptionHandler = new ExceptionHandler();

        $this->sut = new ServerThread($receiver, $this->exceptionHandler);
    }

    public function testUnhandledException(): void
    {
        $exception = new Exception('test exception');
        $this->queue[] = new CommandException($exception);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('test exception');

        $this->sut->run();
    }

    public function testHardStopCommand(): void
    {
        $this->exceptionHandler->register(
            HardStopExceptionCommand::class,
            HardStopException::class,
            function () {
                return new HardStopServerCommand($this->sut);
            }
        );
        $this->queue = [
            new EmptyCommand(),
            new EmptyCommand(),
            new HardStopExceptionCommand(),
            new EmptyCommand(),
        ];

        $this->sut->run();

        $this->tester->assertNotEmpty($this->queue, 'Очередь команд обработана не полностью');
        $this->tester->assertCount(1, $this->queue, 'Осталось необработанной одна команда');
        $this->tester->assertInstanceOf(EmptyCommand::class, $this->queue[0], 'И это последняя тестовая команда');
    }

    public function testSoftStopCommand(): void
    {
        $this->exceptionHandler->register(
            SoftStopExceptionCommand::class,
            SoftStopException::class,
            function () {
                return new SoftStopServerCommand($this->sut);
            }
        );
        $this->queue = [
            new EmptyCommand(),
            new EmptyCommand(),
            new SoftStopExceptionCommand(),
            new EmptyCommand(),
        ];

        $this->sut->run();

        $this->tester->assertEmpty($this->queue, 'Очередь команд обработана полностью');
    }
}
